const express = require('express');
const mysql = require('mysql2/promise');
const xml2js = require('xml2js');
const cors = require('cors');

const app = express();
const port = process.env.PORT || 3004;

app.use(cors());

// Middleware to parse raw body for XML
app.use(express.text({ type: 'application/xml' }));

// Database connection pool
const pool = mysql.createPool({
    host: process.env.DB_HOST || 'localhost',
    user: process.env.DB_USER || 'sidagas_user',
    password: process.env.DB_PASS || 'sidagas_pass',
    database: process.env.DB_NAME || 'finance_db',
    waitForConnections: true,
    connectionLimit: 10,
    queueLimit: 0
});

// Init DB
async function initDB() {
    const conn = await pool.getConnection();
    await conn.query(`
        CREATE TABLE IF NOT EXISTS transactions (
            id INT AUTO_INCREMENT PRIMARY KEY,
            order_id INT NOT NULL,
            amount DECIMAL(10, 2) NOT NULL,
            method VARCHAR(50) NOT NULL,
            status VARCHAR(50) DEFAULT 'verified',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    `);
    conn.release();
    console.log('[Finance Service] Database initialized');
}

// REST API endpoint accepting XML
app.post('/verify', async (req, res) => {
    try {
        const xmlData = req.body;
        console.log('[Finance Service] Received XML Payload:', xmlData);

        // Parse XML to JSON
        const parser = new xml2js.Parser({ explicitArray: false });
        const jsonData = await parser.parseStringPromise(xmlData);
        
        const transaction = jsonData.Transaction;
        
        if (!transaction || !transaction.order_id || !transaction.amount) {
            return res.status(400).send('<Response><Status>Error</Status><Message>Invalid XML format</Message></Response>');
        }

        // Save to Database
        const [result] = await pool.query(
            'INSERT INTO transactions (order_id, amount, method, status) VALUES (?, ?, ?, ?)',
            [transaction.order_id, transaction.amount, transaction.method || 'QRIS', 'verified']
        );

        console.log(`[Finance Service] Transaction saved with ID: ${result.insertId}`);

        // Return XML Response
        const builder = new xml2js.Builder({ rootName: 'Response' });
        const responseXml = builder.buildObject({
            Status: 'Success',
            TransactionId: result.insertId,
            Message: 'Payment verified successfully'
        });

        res.set('Content-Type', 'application/xml');
        res.send(responseXml);

    } catch (error) {
        console.error('[Finance Service] Error processing request:', error);
        res.status(500).send('<Response><Status>Error</Status><Message>Internal Server Error</Message></Response>');
    }
});

// Initialize and start server
async function startServer() {
    await initDB();
    app.listen(port, () => {
        console.log(`[Finance Service] REST API listening at http://localhost:${port}`);
    });
}

startServer().catch(console.error);
