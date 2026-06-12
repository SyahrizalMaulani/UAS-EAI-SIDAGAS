@extends('layouts.admin')

@section('header_title', 'Manajemen Stok & Penjadwalan')

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

    <!-- Panel Stok (Inventory) -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-6 border-b border-gray-100 flex justify-between items-center">
            <h3 class="text-lg font-semibold text-gray-800"><i class="fa-solid fa-boxes-stacked mr-2 text-blue-500"></i>Stok Pabrik</h3>
            <button class="text-sm bg-slate-100 hover:bg-slate-200 text-slate-700 px-3 py-1 rounded transition">Refresh</button>
        </div>
        <div class="p-6">
            <div id="inventoryContainer" class="space-y-4">
                <div class="text-center text-gray-400 py-4"><i class="fa-solid fa-spinner fa-spin"></i> Memuat stok...</div>
            </div>
        </div>
    </div>

    <!-- Panel Penjadwalan Delivery -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden flex flex-col h-full">
        <div class="p-6 border-b border-gray-100">
            <h3 class="text-lg font-semibold text-gray-800"><i class="fa-solid fa-truck-fast mr-2 text-indigo-500"></i>Antrean Pengiriman (Delivery)</h3>
            <p class="text-xs text-gray-500 mt-1">Daftar pesanan yang otomatis dibuat saat stok tersedia.</p>
        </div>
        <div class="flex-1 p-6 bg-slate-50 overflow-y-auto">
            <div id="deliveryQueue" class="space-y-3">
                <div class="text-center text-gray-400 py-4"><i class="fa-solid fa-spinner fa-spin"></i> Memuat jadwal...</div>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
    const API_GATEWAY_URL = 'http://localhost:3000';

    async function loadData() {
        // 1. Load Inventory
        try {
            const invRes = await axios.post(`${API_GATEWAY_URL}/inventory`, {
                query: `query { getInventory { id item_name stock } }`
            });
            const inventory = invRes.data.data.getInventory || [];
            
            let invHtml = '';
            inventory.forEach(item => {
                let barColor = item.stock > 50 ? 'bg-green-500' : (item.stock > 20 ? 'bg-yellow-400' : 'bg-red-500');
                let percentage = Math.min(100, (item.stock / 200) * 100); // Asumsi max 200
                
                invHtml += `
                    <div class="border border-gray-100 rounded-lg p-4">
                        <div class="flex justify-between items-center mb-2">
                            <span class="font-medium text-gray-800">${item.item_name}</span>
                            <span class="font-bold text-lg text-gray-700">${item.stock} Unit</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2.5">
                            <div class="${barColor} h-2.5 rounded-full transition-all duration-1000" style="width: ${percentage}%"></div>
                        </div>
                    </div>
                `;
            });
            document.getElementById('inventoryContainer').innerHTML = invHtml || '<p class="text-center text-gray-500">Tidak ada item.</p>';
        } catch(err) {
            console.error(err);
        }

        // 2. Load Deliveries
        try {
            const delRes = await axios.post(`${API_GATEWAY_URL}/delivery`, {
                query: `query { getDeliveries { id order_id customer_name status } }`
            });
            const deliveries = delRes.data.data.getDeliveries || [];
            
            let delHtml = '';
            deliveries.sort((a,b) => b.id - a.id).forEach(d => {
                let statusBadge = d.status === 'scheduled' 
                    ? `<span class="bg-yellow-100 text-yellow-800 text-xs px-2 py-1 rounded border border-yellow-200">Dijadwalkan</span>`
                    : `<span class="bg-green-100 text-green-800 text-xs px-2 py-1 rounded border border-green-200">${d.status}</span>`;

                delHtml += `
                    <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-200 flex justify-between items-center hover:border-indigo-300 transition cursor-pointer">
                        <div>
                            <div class="flex items-center gap-2 mb-1">
                                <span class="font-bold text-gray-800">${d.customer_name}</span>
                                ${statusBadge}
                            </div>
                            <p class="text-xs text-gray-500">Task ID: #DLV-${d.id} • Ref Order: #ORD-${d.order_id}</p>
                        </div>
                        <div>
                            <button class="w-8 h-8 rounded-full bg-slate-100 text-slate-600 hover:bg-indigo-600 hover:text-white transition flex items-center justify-center" title="Tugaskan Driver">
                                <i class="fa-solid fa-user-plus text-xs"></i>
                            </button>
                        </div>
                    </div>
                `;
            });
            document.getElementById('deliveryQueue').innerHTML = delHtml || '<p class="text-center text-gray-500 text-sm mt-4">Belum ada jadwal pengiriman baru.</p>';
        } catch(err) {
            console.error(err);
        }
    }

    document.addEventListener('DOMContentLoaded', loadData);
</script>
@endpush
