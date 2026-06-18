const express = require('express');
const { graphqlHTTP } = require('express-graphql');
const { buildSchema } = require('graphql');
const mysql = require('mysql2/promise');
const amqp = require('amqplib');
const cors = require('cors');

const app = express();
const port = process.env.PORT || 3003;

app.use(cors());

// Database connection pool
const pool = mysql.createPool({
    host: process.env.DB_HOST || 'localhost',
    user: process.env.DB_USER || 'sidagas_user',
    password: process.env.DB_PASS || 'sidagas_pass',
    database: process.env.DB_NAME || 'delivery_db',
    waitForConnections: true,
    connectionLimit: 10,
    queueLimit: 0
});

// Init DB
async function initDB() {
    const conn = await pool.getConnection();
    await conn.query(`
        CREATE TABLE IF NOT EXISTS deliveries (
            id INT AUTO_INCREMENT PRIMARY KEY,
            order_id INT NOT NULL,
            customer_name VARCHAR(255) NOT NULL,
            address VARCHAR(255) DEFAULT 'Alamat belum diset',
            status VARCHAR(50) DEFAULT 'scheduled',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    `);
    conn.release();
    console.log('[Delivery Service] Database initialized');
}

// RabbitMQ Connection & Publish-Subscribe logic
let channel;
async function initRabbitMQ() {
    try {
        const amqpServer = process.env.RABBITMQ_URL || 'amqp://localhost:5672';
        const connection = await amqp.connect(amqpServer);
        channel = await connection.createChannel();
        
        await channel.assertQueue('order.ready', { durable: true });

        console.log('[Delivery Service] Connected to RabbitMQ');

        // Consume 'order.ready'
        channel.consume('order.ready', async (msg) => {
            if (msg !== null) {
                const orderData = JSON.parse(msg.content.toString());
                console.log(`[Delivery Service] Received order.ready for Order ID: ${orderData.id}`);

                try {
                    // Create delivery schedule
                    await pool.query(
                        'INSERT INTO deliveries (order_id, customer_name, status) VALUES (?, ?, ?)',
                        [orderData.id, orderData.customer_name, 'scheduled']
                    );
                    
                    console.log(`[Delivery Service] Delivery scheduled for Order ID: ${orderData.id}`);
                    channel.ack(msg);
                } catch (err) {
                    console.error('[Delivery Service] Error scheduling delivery:', err);
                    channel.nack(msg, false, false);
                }
            }
        });

    } catch (error) {
        console.error('[Delivery Service] RabbitMQ Connection Error:', error);
        setTimeout(initRabbitMQ, 5000); // Retry after 5 seconds
    }
}

// Load modular schema and resolvers
const schema = require('./schema');
const getResolvers = require('./resolvers');
const root = getResolvers(pool);

app.use('/graphql', graphqlHTTP({
    schema: schema,
    rootValue: root,
    graphiql: true,
}));

// Initialize and start server
async function startServer() {
    await initDB();
    await initRabbitMQ();
    app.listen(port, () => {
        console.log(`[Delivery Service] listening at http://localhost:${port}/graphql`);
    });
}

startServer().catch(console.error);
