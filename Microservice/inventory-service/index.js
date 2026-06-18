const express = require('express');
const { graphqlHTTP } = require('express-graphql');
const { buildSchema } = require('graphql');
const mysql = require('mysql2/promise');
const amqp = require('amqplib');
const cors = require('cors');

const app = express();
const port = process.env.PORT || 3002;

app.use(cors());

// Database connection pool
const pool = mysql.createPool({
    host: process.env.DB_HOST || 'localhost',
    user: process.env.DB_USER || 'sidagas_user',
    password: process.env.DB_PASS || 'sidagas_pass',
    database: process.env.DB_NAME || 'inventory_db',
    waitForConnections: true,
    connectionLimit: 10,
    queueLimit: 0
});

// Init DB
async function initDB() {
    const conn = await pool.getConnection();
    await conn.query(`
        CREATE TABLE IF NOT EXISTS inventory (
            id INT AUTO_INCREMENT PRIMARY KEY,
            item_name VARCHAR(255) NOT NULL UNIQUE,
            stock INT NOT NULL DEFAULT 0,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )
    `);

    // Insert dummy data
    await conn.query(`INSERT IGNORE INTO inventory (item_name, stock) VALUES ('Galon Aqua', 100)`);
    await conn.query(`INSERT IGNORE INTO inventory (item_name, stock) VALUES ('Gas LPG 3Kg', 50)`);

    conn.release();
    console.log('[Inventory Service] Database initialized');
}

// RabbitMQ Connection & Publish-Subscribe logic
let channel;
async function initRabbitMQ() {
    try {
        const amqpServer = process.env.RABBITMQ_URL || 'amqp://localhost:5672';
        const connection = await amqp.connect(amqpServer);
        channel = await connection.createChannel();

        await channel.assertQueue('order.created', { durable: true });
        await channel.assertQueue('order.ready', { durable: true });

        console.log('[Inventory Service] Connected to RabbitMQ');

        // Consume 'order.created'
        channel.consume('order.created', async (msg) => {
            if (msg !== null) {
                const orderData = JSON.parse(msg.content.toString());
                console.log(`[Inventory Service] Received order.created for Order ID: ${orderData.id}`);

                try {
                    // Update stock
                    await pool.query(
                        'UPDATE inventory SET stock = stock - ? WHERE item_name = ? AND stock >= ?',
                        [orderData.quantity, orderData.item_name, orderData.quantity]
                    );

                    console.log(`[Inventory Service] Stock updated for ${orderData.item_name}`);

                    // Publish 'order.ready'
                    orderData.status = 'ready_for_delivery';
                    channel.sendToQueue('order.ready', Buffer.from(JSON.stringify(orderData)), { persistent: true });
                    console.log(`[Inventory Service] Published 'order.ready' event for Order ID: ${orderData.id}`);

                    channel.ack(msg);
                } catch (err) {
                    console.error('[Inventory Service] Error updating stock:', err);
                    // Decide whether to ack or nack based on error type (e.g. out of stock vs db error)
                    channel.nack(msg, false, false); // Dead-letter or retry later in real system
                }
            }
        });

    } catch (error) {
        console.error('[Inventory Service] RabbitMQ Connection Error:', error);
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
        console.log(`[Inventory Service] listening at http://localhost:${port}/graphql`);
    });
}

startServer().catch(console.error);
