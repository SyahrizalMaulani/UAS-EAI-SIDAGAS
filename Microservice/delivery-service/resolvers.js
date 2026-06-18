module.exports = function getResolvers(pool) {
    return {
        getDeliveries: async () => {
            try {
                const [rows] = await pool.query('SELECT * FROM deliveries');
                return rows;
            } catch (err) {
                throw new Error('Gagal mengambil data pengiriman: ' + err.message);
            }
        },
        getDelivery: async ({ id }) => {
            try {
                const [rows] = await pool.query('SELECT * FROM deliveries WHERE id = ?', [id]);
                if (rows.length === 0) throw new Error('Pengiriman tidak ditemukan');
                return rows[0];
            } catch (err) {
                throw new Error('Gagal mengambil data pengiriman: ' + err.message);
            }
        },
        updateDeliveryStatus: async ({ id, status }) => {
            try {
                const [updateResult] = await pool.query(
                    'UPDATE deliveries SET status = ? WHERE id = ?',
                    [status, id]
                );
                if (updateResult.affectedRows === 0) throw new Error('Pengiriman tidak ditemukan');
                const [rows] = await pool.query('SELECT * FROM deliveries WHERE id = ?', [id]);
                return rows[0];
            } catch (err) {
                throw new Error('Gagal mengupdate status pengiriman: ' + err.message);
            }
        },
        deleteDelivery: async ({ id }) => {
            try {
                const [result] = await pool.query('DELETE FROM deliveries WHERE id = ?', [id]);
                if (result.affectedRows === 0) throw new Error('Pengiriman tidak ditemukan');
                return `Pengiriman dengan ID ${id} berhasil dihapus`;
            } catch (err) {
                throw new Error('Gagal menghapus pengiriman: ' + err.message);
            }
        }
    };
};
