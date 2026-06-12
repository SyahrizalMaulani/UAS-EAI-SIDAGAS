@extends('layouts.pelanggan')

@section('content')
<div class="mb-8">
    <h1 class="text-3xl font-bold text-gray-900">Katalog Produk</h1>
    <p class="text-gray-500 mt-2">Pesan kebutuhan air minum dan gas Anda sekarang, kami antar sampai depan pintu.</p>
</div>

<!-- Grid Produk -->
<div id="catalogGrid" class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 md:gap-6">
    <div class="col-span-full text-center py-12">
        <i class="fa-solid fa-spinner fa-spin text-3xl text-blue-500 mb-4"></i>
        <p class="text-gray-500">Memuat katalog produk...</p>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const API_GATEWAY_URL = 'http://localhost:3000';
    let cart = JSON.parse(localStorage.getItem('sidagas_cart')) || [];

    async function loadCatalog() {
        updateCartBadge();
        try {
            const res = await axios.post(`${API_GATEWAY_URL}/inventory`, {
                query: `query { getInventory { id item_name stock } }`
            });
            const inventory = res.data.data.getInventory || [];
            
            let html = '';
            inventory.forEach(item => {
                // Harga dummy, karena tidak ada di tabel aslinya
                let price = item.item_name.toLowerCase().includes('aqua') ? 20000 : 18000;
                
                // Gunakan gambar asli
                let imageSrc = item.item_name.toLowerCase().includes('gas') 
                    ? "{{ asset('img/gas.jpg') }}" 
                    : "{{ asset('img/galon.jpeg') }}";

                let outOfStock = item.stock <= 0;

                html += `
                    <div class="bg-white rounded-2xl p-4 md:p-5 border border-gray-100 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300 relative group">
                        
                        ${outOfStock ? '<div class="absolute top-3 right-3 bg-red-100 text-red-600 text-[10px] font-bold px-2 py-1 rounded-full z-10">HABIS</div>' : ''}
                        
                        <!-- Gambar Produk -->
                        <div class="w-full h-32 md:h-40 rounded-xl mb-4 flex items-center justify-center overflow-hidden">
                            <img src="${imageSrc}" alt="${item.item_name}" class="w-full h-full object-contain group-hover:scale-105 transition duration-500">
                        </div>
                        
                        <h3 class="font-bold text-gray-800 text-sm md:text-base leading-tight mb-1 h-10 overflow-hidden">${item.item_name}</h3>
                        <p class="text-blue-600 font-black text-lg mb-4">Rp ${price.toLocaleString('id-ID')}</p>
                        
                        <div class="flex items-center gap-2">
                            <input type="number" id="qty-${item.id}" value="1" min="1" max="${item.stock}" ${outOfStock ? 'disabled' : ''} class="w-16 h-10 border border-gray-200 rounded-lg text-center font-medium focus:ring-2 focus:ring-blue-500 focus:outline-none">
                            <button onclick="addToCart(${item.id}, '${item.item_name}', ${price})" ${outOfStock ? 'disabled' : ''} class="flex-1 h-10 ${outOfStock ? 'bg-gray-100 text-gray-400' : 'bg-gray-900 hover:bg-black text-white'} rounded-lg text-sm font-bold transition flex items-center justify-center gap-2">
                                <i class="fa-solid fa-plus"></i> <span class="hidden md:inline">Keranjang</span>
                            </button>
                        </div>
                    </div>
                `;
            });
            document.getElementById('catalogGrid').innerHTML = html;
        } catch (err) {
            console.error(err);
            document.getElementById('catalogGrid').innerHTML = '<div class="col-span-full text-center text-red-500 py-10">Gagal terhubung ke API Gateway.</div>';
        }
    }

    function addToCart(id, name, price) {
        const qty = parseInt(document.getElementById(`qty-${id}`).value);
        if(qty < 1) return;

        // Cek apakah item sudah ada di keranjang
        const existing = cart.find(i => i.id === id);
        if(existing) {
            existing.qty += qty;
        } else {
            cart.push({ id, name, price, qty });
        }
        
        localStorage.setItem('sidagas_cart', JSON.stringify(cart));
        updateCartBadge();
        
        const Toast = Swal.mixin({ toast: true, position: 'bottom-end', showConfirmButton: false, timer: 2000 });
        Toast.fire({ icon: 'success', title: `${qty} ${name} ditambahkan!` });
    }

    function updateCartBadge() {
        const badge = document.getElementById('cart-badge');
        const totalItems = cart.reduce((sum, item) => sum + item.qty, 0);
        if(totalItems > 0) {
            badge.innerText = totalItems;
            badge.classList.remove('hidden');
        } else {
            badge.classList.add('hidden');
        }
    }

    document.addEventListener('DOMContentLoaded', loadCatalog);
</script>
@endpush
