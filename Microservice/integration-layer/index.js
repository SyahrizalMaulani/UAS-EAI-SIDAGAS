const express = require('express');
const { createProxyMiddleware } = require('http-proxy-middleware');
const axios = require('axios');
const xml2js = require('xml2js');
const cors = require('cors');

const app = express();
const port = process.env.PORT || 3000;

// Content-Based Router Map
const routes = {
    '/order': process.env.ORDER_SERVICE_URL || 'http://localhost:3001',
    '/inventory': process.env.INVENTORY_SERVICE_URL || 'http://localhost:3002',
    '/delivery': process.env.DELIVERY_SERVICE_URL || 'http://localhost:3003'
};

app.use(cors());

// Middleware for parsing JSON for specific routes (Finance needs custom handling)
app.use('/finance', express.json());

// Message Translator: JSON to XML for Finance Service
app.post('/finance/verify', async (req, res) => {
    try {
        const financeUrl = process.env.FINANCE_SERVICE_URL || 'http://localhost:3004';
        const payload = req.body;

        // Message Translator Pattern: Convert JSON to XML
        const builder = new xml2js.Builder({ rootName: 'Transaction' });
        const xmlPayload = builder.buildObject(payload);

        console.log('[Integration Layer] Translated JSON to XML:', xmlPayload);

        const response = await axios.post(`${financeUrl}/verify`, xmlPayload, {
            headers: {
                'Content-Type': 'application/xml'
            }
        });

        // Convert response XML back to JSON if necessary, or just send back raw
        res.status(response.status).send(response.data);
    } catch (error) {
        console.error('[Integration Layer] Error in Finance routing:', error.message);
        res.status(500).json({ error: 'Finance Service Error' });
    }
});

// Setup Proxies for GraphQL endpoints
for (const [path, target] of Object.entries(routes)) {
    app.use(path, createProxyMiddleware({
        target,
        changeOrigin: true,
        pathRewrite: {
            [`^${path}`]: '/graphql', // rewrite path to point to /graphql on target
        },
    }));
}

// Health check endpoint
app.get('/health', (req, res) => {
    res.json({ status: 'API Gateway is running' });
});

app.listen(port, () => {
    console.log(`[Integration Layer] API Gateway listening at http://localhost:${port}`);
});
