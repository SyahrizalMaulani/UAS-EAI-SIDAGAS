module.exports = function getResolvers(pool) {
    return {
        getInventory: async () => {
            try {
                const [rows] = await pool.query('SELECT * FROM inventory');
                return rows;
            } catch (err) {
                throw new Error('Gagal mengambil data inventory: ' + err.message);
            }
        },
        checkStock: async ({ item_name }) => {
            try {
                const [rows] = await pool.query('SELECT * FROM inventory WHERE item_name = ?', [item_name]);
                if (rows.length === 0) throw new Error('Item tidak ditemukan');
                return rows[0];
            } catch (err) {
                throw new Error('Gagal mengecek stok: ' + err.message);
            }
        },
        updateStock: async ({ item_name, amount }) => {
            try {
                await pool.query(
                    'INSERT INTO inventory (item_name, stock) VALUES (?, ?) ON DUPLICATE KEY UPDATE stock = stock + ?',
                    [item_name, amount, amount]
                );
                const [rows] = await pool.query('SELECT * FROM inventory WHERE item_name = ?', [item_name]);
                return rows[0];
            } catch (err) {
                throw new Error('Gagal mengupdate stok: ' + err.message);
            }
        },
        deleteStock: async ({ item_name }) => {
            try {
                const [result] = await pool.query('DELETE FROM inventory WHERE item_name = ?', [item_name]);
                if (result.affectedRows === 0) throw new Error('Item tidak ditemukan');
                return `Item ${item_name} berhasil dihapus dari inventory`;
            } catch (err) {
                throw new Error('Gagal menghapus stok: ' + err.message);
            }
        }
    };
};
