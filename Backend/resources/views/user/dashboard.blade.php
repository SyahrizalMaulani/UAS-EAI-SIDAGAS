@extends('layouts.app')

@section('content')
<div class="space-y-8">

    <!-- Alert Error Jika API Mati -->
    @if(session('error') || isset($error))
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
        <strong class="font-bold">Oops!</strong>
        <span class="block sm:inline">{{ session('error') ?? $error }}</span>
    </div>
    @endif

    <!-- Seksi 1: Katalog Produk (Inventory) -->
    <section>
        <h2 class="text-2xl font-bold text-gray-800 mb-4 border-b pb-2">Katalog Produk</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-6">
            @forelse($daftarProduk as $produk)
            <div class="bg-white rounded-lg shadow-md p-6 border-t-4 border-blue-500 hover:shadow-lg transition">
                <h3 class="text-lg font-semibold text-gray-800">{{ $produk['item_name'] }}</h3>
                <p class="text-sm text-gray-500 mt-2">Stok Tersedia:</p>
                <p class="text-3xl font-bold {{ $produk['stock'] > 0 ? 'text-green-600' : 'text-red-500' }}">
                    {{ $produk['stock'] }}
                </p>
            </div>
            @empty
            <div class="col-span-full text-center text-gray-500 py-4">Data katalog tidak tersedia atau layanan sedang gangguan.</div>
            @endforelse
        </div>
    </section>

    <!-- Seksi 2: Form Buat Pesanan -->
    <section id="buat-pesanan" class="bg-white rounded-lg shadow-md p-6">
        <h2 class="text-2xl font-bold text-gray-800 mb-4 border-b pb-2">Buat Pesanan Baru</h2>
        <form id="orderForm" class="space-y-4 max-w-2xl">
            <!-- Nama Pelanggan -->
            <div>
                <label for="customer_name" class="block text-sm font-medium text-gray-700">Nama Pelanggan</label>
                <input type="text" id="customer_name" name="customer_name" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 border p-2">
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Pilihan Produk -->
                <div>
                    <label for="item_name" class="block text-sm font-medium text-gray-700">Pilih Produk</label>
                    <select id="item_name" name="item_name" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 border p-2 bg-white">
                        <option value="" disabled selected>-- Pilih Produk --</option>
                        @foreach($daftarProduk as $produk)
                            <option value="{{ $produk['item_name'] }}">{{ $produk['item_name'] }} (Stok: {{ $produk['stock'] }})</option>
                        @endforeach
                    </select>
                </div>

                <!-- Jumlah -->
                <div>
                    <label for="quantity" class="block text-sm font-medium text-gray-700">Jumlah</label>
                    <input type="number" id="quantity" name="quantity" min="1" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 border p-2">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Metode Pengambilan -->
                <div>
                    <label for="delivery_method" class="block text-sm font-medium text-gray-700">Metode Pengambilan</label>
                    <select id="delivery_method" name="delivery_method" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 border p-2 bg-white">
                        <option value="delivery">Delivery (Diantar)</option>
                        <option value="pickup">Ambil Sendiri</option>
                    </select>
                </div>

                <!-- Metode Pembayaran -->
                <div>
                    <label for="payment_method" class="block text-sm font-medium text-gray-700">Metode Pembayaran</label>
                    <select id="payment_method" name="payment_method" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 border p-2 bg-white">
                        <option value="Cash">Tunai (Cash)</option>
                        <option value="Transfer">Transfer Bank</option>
                        <option value="QRIS">QRIS</option>
                    </select>
                </div>
            </div>

            <!-- Submit Button -->
            <div class="pt-4">
                <button type="submit" id="btnSubmit" class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition">
                    Pesan Sekarang
                </button>
            </div>
        </form>
    </section>

    <!-- Seksi 3: Riwayat Pesanan -->
    <section id="riwayat" class="bg-white rounded-lg shadow-md p-6">
        <h2 class="text-2xl font-bold text-gray-800 mb-4 border-b pb-2">Riwayat Pesanan</h2>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 border">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pelanggan</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Produk</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Qty</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($riwayatPesanan as $pesanan)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">#{{ $pesanan['id'] }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ \Carbon\Carbon::parse($pesanan['created_at'])->format('d M Y H:i') }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $pesanan['customer_name'] }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $pesanan['item_name'] }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $pesanan['quantity'] }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            @if($pesanan['status'] == 'pending')
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">Menunggu</span>
                            @elseif($pesanan['status'] == 'ready_for_delivery')
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">Siap Kirim</span>
                            @else
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">{{ ucfirst($pesanan['status']) }}</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500">Belum ada riwayat pesanan.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const orderForm = document.getElementById('orderForm');
    const btnSubmit = document.getElementById('btnSubmit');

    // Pastikan URL API Gateway disesuaikan dengan environment client
    const API_GATEWAY_URL = 'http://localhost:3000';

    orderForm.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        // Ambil nilai dari form
        const customerName = document.getElementById('customer_name').value;
        const itemName = document.getElementById('item_name').value;
        const quantity = parseInt(document.getElementById('quantity').value);
        const paymentMethod = document.getElementById('payment_method').value;

        // Validasi sederhana
        if(!itemName) {
            Swal.fire('Error', 'Silakan pilih produk terlebih dahulu.', 'error');
            return;
        }

        // Disable button & ubah teks
        btnSubmit.disabled = true;
        const originalBtnText = btnSubmit.innerHTML;
        btnSubmit.innerHTML = '<svg class="animate-spin h-5 w-5 mr-3 text-white inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Memproses...';

        // 1. Susun GraphQL Mutation untuk Order Service
        const createOrderMutation = `
            mutation {
                createOrder(customer_name: "${customerName}", item_name: "${itemName}", quantity: ${quantity}) {
                    id
                    status
                }
            }
        `;

        try {
            // Tembak GraphQL Endpoint di API Gateway untuk Order
            const orderResponse = await axios.post(`${API_GATEWAY_URL}/order`, {
                query: createOrderMutation
            });

            const orderData = orderResponse.data;
            if (orderData.errors) {
                throw new Error(orderData.errors[0].message);
            }

            const orderId = orderData.data.createOrder.id;

            // 2. Simulasi Tembak Endpoint Finance API via REST (Heterogenitas XML)
            // Hitung total asal-asalan untuk simulasi
            const amount = quantity * 20000; 
            const financePayload = {
                order_id: orderId,
                amount: amount,
                method: paymentMethod
            };

            // Karena API Gateway akan menerima JSON dan mengkonversi ke XML untuk Finance Service
            await axios.post(`${API_GATEWAY_URL}/finance/verify`, financePayload);

            // Berhasil
            Swal.fire({
                title: 'Pesanan Berhasil!',
                text: 'Pesanan Anda sedang diproses. Halaman akan dimuat ulang.',
                icon: 'success',
                timer: 2000,
                showConfirmButton: false
            }).then(() => {
                // Reload untuk meng-update tabel riwayat (via SSR Laravel)
                // Idealnya bisa via JS DOM manipulation, tapi reload lebih simple
                window.location.reload(); 
            });

            orderForm.reset();

        } catch (error) {
            console.error(error);
            Swal.fire({
                title: 'Gagal Membuat Pesanan',
                text: error.message || 'Terjadi kesalahan pada server. Coba lagi nanti.',
                icon: 'error'
            });
        } finally {
            // Enable button again
            btnSubmit.disabled = false;
            btnSubmit.innerHTML = originalBtnText;
        }
    });
});
</script>
@endpush
