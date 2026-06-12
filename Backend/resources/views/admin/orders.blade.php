@extends('layouts.admin')

@section('header_title', 'Manajemen Transaksi & Pesanan')

@section('content')
<div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="p-6 border-b border-gray-100 flex flex-col sm:flex-row justify-between items-center gap-4">
        <h3 class="text-lg font-semibold text-gray-800">Riwayat Pesanan</h3>
        <div class="flex gap-2">
            <input type="text" placeholder="Cari pesanan..." class="px-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            <button class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-blue-700 transition">Filter</button>
        </div>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wider">
                    <th class="px-6 py-4 font-medium">Order ID</th>
                    <th class="px-6 py-4 font-medium">Pelanggan</th>
                    <th class="px-6 py-4 font-medium">Produk</th>
                    <th class="px-6 py-4 font-medium">Jumlah</th>
                    <th class="px-6 py-4 font-medium">Status Pesanan</th>
                    <th class="px-6 py-4 font-medium text-right">Aksi</th>
                </tr>
            </thead>
            <tbody id="ordersTableBody" class="divide-y divide-gray-100 text-sm text-gray-700">
                <tr>
                    <td colspan="6" class="px-6 py-8 text-center text-gray-400">
                        <i class="fa-solid fa-circle-notch fa-spin text-2xl mb-2"></i>
                        <p>Memuat data dari API Gateway...</p>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const API_GATEWAY_URL = 'http://localhost:3000';

    async function loadOrders() {
        try {
            const res = await axios.post(`${API_GATEWAY_URL}/order`, {
                query: `
                    query {
                        getOrders {
                            id
                            customer_name
                            item_name
                            quantity
                            status
                        }
                    }
                `
            });
            const orders = res.data.data.getOrders || [];
            const tbody = document.getElementById('ordersTableBody');
            
            if(orders.length === 0) {
                tbody.innerHTML = `<tr><td colspan="6" class="px-6 py-4 text-center">Belum ada pesanan</td></tr>`;
                return;
            }

            let html = '';
            // Sort by ID descending (newest first)
            orders.sort((a,b) => b.id - a.id).forEach(order => {
                let badgeColor = 'bg-gray-100 text-gray-800';
                if(order.status === 'pending') badgeColor = 'bg-yellow-100 text-yellow-800';
                if(order.status === 'ready_for_delivery') badgeColor = 'bg-blue-100 text-blue-800';
                if(order.status === 'completed') badgeColor = 'bg-green-100 text-green-800';

                html += `
                    <tr class="hover:bg-slate-50 transition">
                        <td class="px-6 py-4 font-medium">#ORD-${order.id}</td>
                        <td class="px-6 py-4 font-medium text-gray-900">${order.customer_name}</td>
                        <td class="px-6 py-4">${order.item_name}</td>
                        <td class="px-6 py-4">${order.quantity}</td>
                        <td class="px-6 py-4">
                            <span class="px-3 py-1 text-xs font-semibold rounded-full ${badgeColor}">
                                ${order.status.replace(/_/g, ' ').toUpperCase()}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <button onclick="verifyPayment(${order.id})" class="text-blue-600 hover:text-blue-900 font-medium text-sm bg-blue-50 px-3 py-1 rounded-md transition hover:bg-blue-100 border border-blue-200">
                                <i class="fa-solid fa-check-double mr-1"></i> Verifikasi Pembayaran
                            </button>
                        </td>
                    </tr>
                `;
            });
            tbody.innerHTML = html;

        } catch (err) {
            console.error(err);
            document.getElementById('ordersTableBody').innerHTML = `<tr><td colspan="6" class="px-6 py-4 text-center text-red-500">Gagal memuat data</td></tr>`;
        }
    }

    // Simulasi aksi Admin memverifikasi pembayaran via GraphQL / REST
    async function verifyPayment(orderId) {
        // Tampilkan prompt untuk metode pembayaran
        const { value: method } = await Swal.fire({
            title: 'Verifikasi Pembayaran',
            text: 'Pilih metode pembayaran yang digunakan:',
            input: 'select',
            inputOptions: {
                'QRIS': 'QRIS',
                'Transfer': 'Transfer Bank',
                'Cash': 'Tunai (Cash)'
            },
            inputPlaceholder: 'Pilih Metode',
            showCancelButton: true,
            confirmButtonText: 'Verifikasi',
            cancelButtonText: 'Batal'
        });

        if (method) {
            // Loading
            Swal.fire({ title: 'Memproses...', allowOutsideClick: false, didOpen: () => { Swal.showLoading(); } });

            try {
                // Di sistem sebenarnya, Admin mem-POST ke Finance Service via API Gateway
                const amount = 50000; // Harga simulasi
                await axios.post(`${API_GATEWAY_URL}/finance/verify`, {
                    order_id: orderId,
                    amount: amount,
                    method: method
                });

                Swal.fire('Berhasil!', `Pembayaran Order #${orderId} telah diverifikasi dengan metode ${method}.`, 'success');
                // loadOrders(); // Refresh table if needed
            } catch (err) {
                console.error(err);
                Swal.fire('Error', 'Gagal memverifikasi pembayaran. Coba lagi.', 'error');
            }
        }
    }

    document.addEventListener('DOMContentLoaded', loadOrders);
</script>
@endpush
