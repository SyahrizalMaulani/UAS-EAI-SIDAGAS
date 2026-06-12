@extends('layouts.admin')

@section('header_title', 'Dashboard Utama')

@section('content')
<div class="space-y-6">
    
    <!-- Notifikasi Stok Menipis (Warning Panel) -->
    <div id="stockWarningPanel" class="hidden bg-red-50 border-l-4 border-red-500 p-4 rounded-r-lg shadow-sm">
        <div class="flex">
            <div class="flex-shrink-0">
                <i class="fa-solid fa-triangle-exclamation text-red-500 mt-1"></i>
            </div>
            <div class="ml-3">
                <h3 class="text-sm font-medium text-red-800">Perhatian: Stok Menipis!</h3>
                <div class="mt-1 text-sm text-red-700">
                    <p id="stockWarningText">Beberapa item di gudang hampir habis. Segera jadwalkan produksi ulang.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Metrik Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Card 1 -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex items-center gap-4 hover:shadow-md transition">
            <div class="w-12 h-12 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center text-xl">
                <i class="fa-solid fa-cart-arrow-down"></i>
            </div>
            <div>
                <p class="text-sm font-medium text-gray-500">Pesanan Hari Ini</p>
                <p class="text-2xl font-bold text-gray-800" id="metric-orders">0</p>
            </div>
        </div>
        <!-- Card 2 -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex items-center gap-4 hover:shadow-md transition">
            <div class="w-12 h-12 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center text-xl">
                <i class="fa-solid fa-truck-fast"></i>
            </div>
            <div>
                <p class="text-sm font-medium text-gray-500">Total Pengiriman</p>
                <p class="text-2xl font-bold text-gray-800" id="metric-deliveries">0</p>
            </div>
        </div>
        <!-- Card 3 -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex items-center gap-4 hover:shadow-md transition">
            <div class="w-12 h-12 rounded-full bg-green-100 text-green-600 flex items-center justify-center text-xl">
                <i class="fa-solid fa-rupiah-sign"></i>
            </div>
            <div>
                <p class="text-sm font-medium text-gray-500">Pendapatan (Estimasi)</p>
                <p class="text-2xl font-bold text-gray-800" id="metric-revenue">Rp 0</p>
            </div>
        </div>
        <!-- Card 4 -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex items-center gap-4 hover:shadow-md transition">
            <div class="w-12 h-12 rounded-full bg-orange-100 text-orange-600 flex items-center justify-center text-xl">
                <i class="fa-solid fa-glass-water"></i>
            </div>
            <div>
                <p class="text-sm font-medium text-gray-500">Total Stok Galon</p>
                <p class="text-2xl font-bold text-gray-800" id="metric-stock">0</p>
            </div>
        </div>
    </div>

    <!-- Charts / Overview (Placeholder) -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Grafik Transaksi Mingguan</h3>
            <div class="h-64 flex items-center justify-center bg-gray-50 rounded-lg border border-dashed border-gray-200">
                <span class="text-gray-400"><i class="fa-solid fa-chart-line mr-2"></i>Area Chart (Integrasi Chart.js)</span>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Aktivitas Terbaru</h3>
            <div class="space-y-4" id="recent-activity">
                <div class="flex items-center gap-3">
                    <div class="w-2 h-2 bg-blue-500 rounded-full"></div>
                    <p class="text-sm text-gray-600"><span class="font-medium text-gray-800">Sistem</span> sedang memuat data dari API Gateway...</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const API_GATEWAY_URL = 'http://localhost:3000';

    // Fetch Overview Data via API Gateway
    async function loadDashboardData() {
        try {
            // 1. Ambil Data Orders
            const orderRes = await axios.post(`${API_GATEWAY_URL}/order`, {
                query: `query { getOrders { id status quantity } }`
            });
            const orders = orderRes.data.data.getOrders || [];
            document.getElementById('metric-orders').innerText = orders.length;
            
            // Hitung estimasi pendapatan kasar (asumsi harga 20.000 / qty)
            let totalRevenue = 0;
            orders.forEach(o => totalRevenue += (o.quantity * 20000));
            document.getElementById('metric-revenue').innerText = 'Rp ' + totalRevenue.toLocaleString('id-ID');

            // 2. Ambil Data Inventory
            const invRes = await axios.post(`${API_GATEWAY_URL}/inventory`, {
                query: `query { getInventory { item_name stock } }`
            });
            const inventory = invRes.data.data.getInventory || [];
            
            let totalGalon = 0;
            let warningText = [];
            inventory.forEach(item => {
                if(item.item_name.toLowerCase().includes('galon')) totalGalon += item.stock;
                if(item.stock < 20) {
                    warningText.push(`Stok <b>${item.item_name}</b> tersisa ${item.stock}`);
                }
            });
            
            document.getElementById('metric-stock').innerText = totalGalon;

            // Tampilkan panel warning jika ada stok nipis
            if(warningText.length > 0) {
                document.getElementById('stockWarningPanel').classList.remove('hidden');
                document.getElementById('stockWarningText').innerHTML = warningText.join('<br>');
            }

            // 3. Ambil Data Delivery
            const delRes = await axios.post(`${API_GATEWAY_URL}/delivery`, {
                query: `query { getDeliveries { id } }`
            });
            const deliveries = delRes.data.data.getDeliveries || [];
            document.getElementById('metric-deliveries').innerText = deliveries.length;

            // Hapus loading text
            document.getElementById('recent-activity').innerHTML = `
                <div class="flex items-center gap-3">
                    <div class="w-2 h-2 bg-green-500 rounded-full"></div>
                    <p class="text-sm text-gray-600">Data termutakhir berhasil disinkronisasi.</p>
                </div>
            `;

        } catch (err) {
            console.error('Gagal mengambil data dashboard:', err);
        }
    }

    // Eksekusi saat halaman dimuat
    document.addEventListener('DOMContentLoaded', loadDashboardData);
</script>
@endpush
