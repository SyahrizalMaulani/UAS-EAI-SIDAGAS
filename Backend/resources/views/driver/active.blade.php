@extends('layouts.driver')

@section('content')
<!-- State 1: Tidak ada tugas -->
<div id="noActiveTask" class="h-full flex flex-col items-center justify-center pt-20 hidden">
    <div class="w-32 h-32 bg-gray-100 rounded-full flex items-center justify-center mb-6">
        <i class="fa-solid fa-truck-ramp-box text-5xl text-gray-300"></i>
    </div>
    <h2 class="text-xl font-bold text-gray-800 mb-2">Tidak Ada Tugas Berjalan</h2>
    <p class="text-center text-gray-500 px-6 mb-8">Silakan ambil tugas pengiriman baru di menu Daftar Tugas.</p>
    <a href="/driver" class="bg-blue-600 text-white font-bold py-3 px-8 rounded-xl shadow-lg shadow-blue-500/30">Cari Tugas</a>
</div>

<!-- State 2: Ada tugas berjalan -->
<div id="activeTask" class="hidden">
    <div class="bg-gradient-to-r from-blue-600 to-indigo-600 -mx-4 -mt-4 p-6 pb-12 text-white">
        <div class="flex justify-between items-start mb-4">
            <span class="bg-white/20 text-white text-xs font-bold px-3 py-1 rounded-full backdrop-blur-sm" id="activeOrderId">ORD-000</span>
            <span class="bg-yellow-400 text-yellow-900 text-xs font-bold px-3 py-1 rounded-full shadow-sm">DALAM PERJALANAN</span>
        </div>
        <h2 class="text-3xl font-black mb-1" id="activeCustomer">Loading...</h2>
        <p class="text-blue-100 opacity-90"><i class="fa-solid fa-location-dot mr-1"></i> <span id="activeAddress">Loading...</span></p>
    </div>

    <!-- Panel Kontrol Utama (Menggantung) -->
    <div class="bg-white rounded-3xl shadow-[0_8px_30px_rgb(0,0,0,0.12)] p-6 -mt-8 relative z-10 border border-gray-100">
        
        <div class="flex justify-between items-center mb-6 pb-6 border-b border-gray-100">
            <div>
                <p class="text-xs text-gray-400 font-bold uppercase tracking-wider mb-1">Barang Bawaan</p>
                <p class="text-lg font-black text-gray-800"><span id="activeQtyDisplay">0</span> <span class="text-sm font-medium text-gray-500">Galon</span></p>
            </div>
            <div class="text-right">
                <p class="text-xs text-gray-400 font-bold uppercase tracking-wider mb-1">Tagihan</p>
                <p class="text-xl font-black text-green-600" id="activePriceDisplay">Rp 0</p>
            </div>
        </div>

        <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-4">Aksi Pengiriman</h3>
        
        <div class="space-y-3">
            <button onclick="callCustomer()" class="w-full bg-green-50 text-green-700 py-4 rounded-2xl font-bold flex justify-center items-center gap-2 border border-green-200 transition active:scale-95">
                <i class="fa-solid fa-phone text-lg"></i> Hubungi Pelanggan
            </button>
            
            <button onclick="markAsDelivered()" class="w-full bg-blue-600 text-white py-4 rounded-2xl font-bold flex justify-center items-center gap-2 shadow-lg shadow-blue-500/30 transition active:scale-95">
                <i class="fa-solid fa-box-open text-lg"></i> Barang Telah Diterima
            </button>
        </div>

    </div>
</div>
@endsection

@push('scripts')
<script>
    const API_GATEWAY_URL = 'http://localhost:3000';

    document.addEventListener('DOMContentLoaded', () => {
        const activeId = localStorage.getItem('activeDeliveryId');
        const orderId = localStorage.getItem('activeOrderId');
        const customerName = localStorage.getItem('activeCustomerName');
        const qty = localStorage.getItem('activeQty') || 1;
        
        if (!activeId) {
            document.getElementById('noActiveTask').classList.remove('hidden');
        } else {
            // Jika ada ID di localstorage, tampilkan UI Active
            document.getElementById('activeTask').classList.remove('hidden');
            
            document.getElementById('activeOrderId').innerText = `ORD-${orderId}`;
            document.getElementById('activeCustomer').innerText = customerName;
            document.getElementById('activeAddress').innerText = "Detail alamat tersedia di Peta";
            document.getElementById('activeQtyDisplay').innerText = qty;
            
            // Asumsi Rp 20.000 per galon
            const price = qty * 20000;
            document.getElementById('activePriceDisplay').innerText = 'Rp ' + price.toLocaleString('id-ID');
        }
    });

    function callCustomer() {
        window.location.href = 'tel:081234567890';
    }

    async function markAsDelivered() {
        const activeId = localStorage.getItem('activeDeliveryId');
        
        // Disable interaction
        Swal.fire({ title: 'Memproses...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

        try {
            // GraphQL Mutation untuk update status pengiriman
            const query = `
                mutation {
                    updateDeliveryStatus(id: ${activeId}, status: "completed") {
                        id
                        status
                    }
                }
            `;
            await axios.post(`${API_GATEWAY_URL}/delivery`, { query: query });

            // Sukses
            localStorage.removeItem('activeDeliveryId');
            localStorage.removeItem('activeOrderId');
            localStorage.removeItem('activeCustomerName');
            localStorage.removeItem('activeQty');
            
            Swal.fire({
                title: 'Pengiriman Selesai!',
                text: 'Kerja bagus! Data telah disinkronkan ke pusat.',
                icon: 'success',
                confirmButtonColor: '#2563eb',
                confirmButtonText: 'Lanjut Tugas Berikutnya'
            }).then(() => {
                window.location.href = '/driver';
            });

        } catch (error) {
            console.error(error);
            // Tetap izinkan selesai meski error agar simulasi lancar
            localStorage.removeItem('activeDeliveryId');
            Swal.fire('Informasi', 'Tugas ditandai selesai secara lokal (Simulasi API Error).', 'success').then(() => window.location.href = '/driver');
        }
    }
</script>
@endpush
