module.exports = function getResolvers(pool, getChannel) {
    return {
        getOrders: async () => {
            try {
                const [rows] = await pool.query('SELECT * FROM orders');
                return rows;
            } catch (err) {
                throw new Error('Gagal mengambil data pesanan: ' + err.message);
            }
        },
        getOrder: async ({ id }) => {
            try {
                const [rows] = await pool.query('SELECT * FROM orders WHERE id = ?', [id]);
                if (rows.length === 0) throw new Error('Order tidak ditemukan');
                return rows[0];
            } catch (err) {
                throw new Error('Gagal mengambil data pesanan: ' + err.message);
            }
        },
        createOrder: async ({ customer_name, item_name, quantity }) => {
            try {
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

                const channel = getChannel();
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
            } catch (err) {
                throw new Error('Gagal membuat pesanan: ' + err.message);
            }
        },
        deleteOrder: async ({ id }) => {
            try {
                const [result] = await pool.query('DELETE FROM orders WHERE id = ?', [id]);
                if (result.affectedRows === 0) throw new Error('Order tidak ditemukan');
                return `Order dengan ID ${id} berhasil dihapus`;
            } catch (err) {
                throw new Error('Gagal menghapus pesanan: ' + err.message);
            }
        }
    };
};
