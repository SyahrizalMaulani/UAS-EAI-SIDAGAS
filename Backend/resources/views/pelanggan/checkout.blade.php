@extends('layouts.pelanggan')

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-900">Checkout Pesanan</h1>
    <p class="text-gray-500 mt-1">Selesaikan pesanan Anda dengan memilih metode pengiriman dan pembayaran.</p>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    
    <!-- Bagian Kiri: Form & Opsi -->
    <div class="lg:col-span-2 space-y-6">
        
        <!-- Alamat Pengiriman -->
        <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm">
            <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center gap-2"><i class="fa-solid fa-map-location-dot text-blue-500"></i> Detail Pengiriman</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Metode Penerimaan</label>
                    <select id="deliveryMethod" class="w-full border border-gray-300 rounded-lg p-3 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                        <option value="delivery">Diantar ke Rumah (Delivery)</option>
                        <option value="pickup">Ambil Sendiri di Toko</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nama Penerima</label>
                    <input type="text" id="customerName" value="{{ session('name') }}" class="w-full border border-gray-300 rounded-lg p-3 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none" required>
                </div>
            </div>
            <div id="addressField">
                <label class="block text-sm font-medium text-gray-700 mb-1">Alamat Lengkap</label>
                <textarea rows="2" class="w-full border border-gray-300 rounded-lg p-3 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none" placeholder="Masukkan alamat lengkap dengan patokan..."></textarea>
            </div>
        </div>

        <!-- Metode Pembayaran -->
        <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm">
            <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center gap-2"><i class="fa-solid fa-wallet text-blue-500"></i> Metode Pembayaran</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                <label class="relative border border-gray-200 rounded-xl p-4 cursor-pointer hover:border-blue-500 transition">
                    <input type="radio" name="paymentMethod" value="Cash" class="absolute top-4 right-4" checked onchange="toggleQris()">
                    <i class="fa-solid fa-money-bill-wave text-2xl text-green-500 mb-2"></i>
                    <p class="font-bold text-gray-800 text-sm">Tunai (COD)</p>
                    <p class="text-xs text-gray-500">Bayar di tempat</p>
                </label>
                
                <label class="relative border border-gray-200 rounded-xl p-4 cursor-pointer hover:border-blue-500 transition">
                    <input type="radio" name="paymentMethod" value="Transfer" class="absolute top-4 right-4" onchange="toggleQris()">
                    <i class="fa-solid fa-building-columns text-2xl text-blue-500 mb-2"></i>
                    <p class="font-bold text-gray-800 text-sm">Transfer Bank</p>
                    <p class="text-xs text-gray-500">BCA, Mandiri, dll</p>
                </label>
                
                <label class="relative border border-gray-200 rounded-xl p-4 cursor-pointer hover:border-blue-500 transition">
                    <input type="radio" name="paymentMethod" value="QRIS" class="absolute top-4 right-4" onchange="toggleQris()">
                    <i class="fa-solid fa-qrcode text-2xl text-indigo-500 mb-2"></i>
                    <p class="font-bold text-gray-800 text-sm">QRIS</p>
                    <p class="text-xs text-gray-500">OVO, Gopay, Dana</p>
                </label>
            </div>

            <!-- Area QRIS (Hidden by default) -->
            <div id="qrisArea" class="mt-6 hidden bg-slate-50 border border-slate-200 rounded-xl p-6 text-center">
                <p class="text-sm font-medium text-gray-700 mb-4">Scan QR Code di bawah ini menggunakan aplikasi E-Wallet Anda:</p>
                <div class="w-48 h-48 bg-white mx-auto border-4 border-white shadow-md rounded-lg p-2 mb-4">
                    <!-- Placeholder QR Code -->
                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=SIDAGAS-PAYMENT" alt="QRIS" class="w-full h-full object-contain">
                </div>
                <p class="text-xs text-gray-500">Sistem akan memverifikasi pembayaran secara otomatis (Simulasi API Gateway).</p>
            </div>
        </div>

    </div>

    <!-- Bagian Kanan: Ringkasan Keranjang -->
    <div>
        <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm sticky top-24">
            <h3 class="text-lg font-bold text-gray-800 mb-4">Ringkasan Belanja</h3>
            
            <div id="cartItemsList" class="space-y-4 mb-6 max-h-60 overflow-y-auto">
                <!-- Item Keranjang muncul di sini -->
                <p class="text-gray-400 text-sm text-center">Keranjang masih kosong.</p>
            </div>
            
            <div class="border-t border-gray-100 pt-4 space-y-2 mb-6">
                <div class="flex justify-between text-sm">
                    <span class="text-gray-500">Subtotal</span>
                    <span class="font-medium" id="cartSubtotal">Rp 0</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-gray-500">Ongkos Kirim</span>
                    <span class="font-medium text-green-600" id="cartShipping">Gratis</span>
                </div>
                <div class="flex justify-between text-lg font-black pt-2 border-t border-gray-100 mt-2">
                    <span class="text-gray-800">Total</span>
                    <span class="text-blue-600" id="cartTotal">Rp 0</span>
                </div>
            </div>

            <button onclick="processCheckout()" id="btnCheckout" class="w-full bg-blue-600 hover:bg-blue-700 text-white py-4 rounded-xl font-bold transition shadow-lg shadow-blue-500/30 flex justify-center items-center gap-2 disabled:bg-gray-300 disabled:shadow-none disabled:cursor-not-allowed">
                Bayar & Buat Pesanan <i class="fa-solid fa-arrow-right"></i>
            </button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const API_GATEWAY_URL = 'http://localhost:3000';
    let cart = JSON.parse(localStorage.getItem('sidagas_cart')) || [];

    function renderCart() {
        const container = document.getElementById('cartItemsList');
        const btnCheckout = document.getElementById('btnCheckout');
        
        if (cart.length === 0) {
            container.innerHTML = '<p class="text-gray-400 text-sm text-center py-4">Keranjang kosong. Yuk belanja dulu!</p>';
            btnCheckout.disabled = true;
            return;
        }

        let html = '';
        let total = 0;

        cart.forEach((item, index) => {
            const itemTotal = item.price * item.qty;
            total += itemTotal;
            html += `
                <div class="flex justify-between items-center text-sm">
                    <div class="flex-1">
                        <p class="font-bold text-gray-800 leading-tight">${item.name}</p>
                        <p class="text-gray-500 text-xs">${item.qty} x Rp ${item.price.toLocaleString('id-ID')}</p>
                    </div>
                    <div class="font-bold text-gray-800">
                        Rp ${itemTotal.toLocaleString('id-ID')}
                    </div>
                    <button onclick="removeCartItem(${index})" class="ml-3 text-red-400 hover:text-red-600"><i class="fa-solid fa-trash"></i></button>
                </div>
            `;
        });

        container.innerHTML = html;
        document.getElementById('cartSubtotal').innerText = `Rp ${total.toLocaleString('id-ID')}`;
        document.getElementById('cartTotal').innerText = `Rp ${total.toLocaleString('id-ID')}`;
        btnCheckout.disabled = false;
        
        // Update badge (from layout)
        const badge = document.getElementById('cart-badge');
        if(badge) {
            badge.innerText = cart.reduce((sum, item) => sum + item.qty, 0);
            badge.classList.remove('hidden');
        }
    }

    function removeCartItem(index) {
        cart.splice(index, 1);
        localStorage.setItem('sidagas_cart', JSON.stringify(cart));
        renderCart();
    }

    function toggleQris() {
        const method = document.querySelector('input[name="paymentMethod"]:checked').value;
        const qrisArea = document.getElementById('qrisArea');
        if (method === 'QRIS') {
            qrisArea.classList.remove('hidden');
        } else {
            qrisArea.classList.add('hidden');
        }
    }

    // Toggle address input based on delivery method
    document.getElementById('deliveryMethod').addEventListener('change', function() {
        const addressField = document.getElementById('addressField');
        if(this.value === 'pickup') {
            addressField.classList.add('hidden');
            document.getElementById('cartShipping').innerText = '-';
        } else {
            addressField.classList.remove('hidden');
            document.getElementById('cartShipping').innerText = 'Gratis';
        }
    });

    async function processCheckout() {
        if(cart.length === 0) return;
        
        const customerName = document.getElementById('customerName').value;
        const paymentMethod = document.querySelector('input[name="paymentMethod"]:checked').value;
        
        if(!customerName) {
            Swal.fire('Oops', 'Nama penerima wajib diisi', 'warning'); return;
        }

        const btn = document.getElementById('btnCheckout');
        btn.disabled = true;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Memproses...';

        try {
            // Karena arsitektur Order Service saat ini (GraphQL) hanya menerima 1 item per request:
            // createOrder(customer_name: String, item_name: String, quantity: Int)
            // Kita akan loop cart dan mengirim mutation satu per satu, atau kirim item pertama saja untuk contoh EAI.
            // Di produksi nyata, GraphQL schema harus mendukung Array/List input.
            
            for (const item of cart) {
                const query = `
                    mutation {
                        createOrder(customer_name: "${customerName}", item_name: "${item.name}", quantity: ${item.qty}) {
                            id
                        }
                    }
                `;
                
                const res = await axios.post(`${API_GATEWAY_URL}/order`, { query: query });
                const orderId = res.data.data.createOrder.id;

                // Tembak verifikasi Finance secara asinkron
                const amount = item.price * item.qty;
                await axios.post(`${API_GATEWAY_URL}/finance/verify`, {
                    order_id: orderId, amount: amount, method: paymentMethod
                }).catch(e => console.log('Finance simulasi error, abaikan'));
            }

            // Bersihkan keranjang
            localStorage.removeItem('sidagas_cart');
            cart = [];
            
            Swal.fire({
                title: 'Pesanan Berhasil!',
                text: 'Terima kasih, pesanan Anda sedang kami proses.',
                icon: 'success',
                confirmButtonColor: '#2563eb'
            }).then(() => {
                window.location.href = '/pelanggan/tracking';
            });

        } catch (error) {
            Swal.fire('Error', 'Gagal memproses pesanan ke server.', 'error');
            btn.disabled = false;
            btn.innerHTML = 'Bayar & Buat Pesanan <i class="fa-solid fa-arrow-right"></i>';
        }
    }

    document.addEventListener('DOMContentLoaded', renderCart);
</script>
@endpush
