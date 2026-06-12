const express = require('express');
const { graphqlHTTP } = require('express-graphql');
const { buildSchema } = require('graphql');
const mysql = require('mysql2/promise');
const amqp = require('amqplib');
const cors = require('cors');

const app = express();
const port = process.env.PORT || 3001;

app.use(cors());

// Database connection pool
const pool = mysql.createPool({
    host: process.env.DB_HOST || 'localhost',
    user: process.env.DB_USER || 'sidagas_user',
    password: process.env.DB_PASS || 'sidagas_pass',
    database: process.env.DB_NAME || 'order_db',
    waitForConnections: true,
    connectionLimit: 10,
    queueLimit: 0
});

// Init DB
async function initDB() {
    const conn = await pool.getConnection();
    await conn.query(`
        CREATE TABLE IF NOT EXISTS orders (
            id INT AUTO_INCREMENT PRIMARY KEY,
            customer_name VARCHAR(255) NOT NULL,
            item_name VARCHAR(255) NOT NULL,
            quantity INT NOT NULL,
            status VARCHAR(50) DEFAULT 'pending',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    `);
    conn.release();
    console.log('[Order Service] Database initialized');
}

// RabbitMQ Connection
let channel;
async function initRabbitMQ() {
    try {
        const amqpServer = process.env.RABBITMQ_URL || 'amqp://localhost:5672';
        const connection = await amqp.connect(amqpServer);
        channel = await connection.createChannel();
        await channel.assertQueue('order.created', { durable: true });
        console.log('[Order Service] Connected to RabbitMQ');
    } catch (error) {
        console.error('[Order Service] RabbitMQ Connection Error:', error);
        setTimeout(initRabbitMQ, 5000); // Retry after 5 seconds
    }
}

// GraphQL Schema
const schema = buildSchema(`
    type Order {
        id: ID!
        customer_name: String!
        item_name: String!
        quantity: Int!
        status: String!
        created_at: String!
    }

    type Query {
        getOrders: [Order]
        getOrder(id: ID!): Order
    }

    type Mutation {
        createOrder(customer_name: String!, item_name: String!, quantity: Int!): Order
    }
`);

// GraphQL Resolvers
const root = {
    getOrders: async () => {
        const [rows] = await pool.query('SELECT * FROM orders');
        return rows;
    },
    getOrder: async ({ id }) => {
        const [rows] = await pool.query('SELECT * FROM orders WHERE id = ?', [id]);
        return rows[0];
    },
    createOrder: async ({ customer_name, item_name, quantity }) => {
        const [result] = await pool.query(
            'INSERT INTO orders (customer_name, item_name, quantity, status) VALUES (?, ?, ?, ?)',
            [customer_name, item_name, quantity, 'pending']
        );
        
        const newOrder = {
            id: result.insertId,
            customer_name,
            item_name,
            quantity,
            status: 'pending',
            created_at: new Date().toISOString()
        };

        // Message Endpoint Pattern: Publish event to RabbitMQ
        if (channel) {
            channel.sendToQueue(
                'order.created',
                Buffer.from(JSON.stringify(newOrder)),
                { persistent: true }
            );
            console.log(`[Order Service] Published 'order.created' event for Order ID: ${newOrder.id}`);
        }

        return newOrder;
    }
};

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
        console.log(`[Order Service] listening at http://localhost:${port}/graphql`);
    });
}

startServer().catch(console.error);
