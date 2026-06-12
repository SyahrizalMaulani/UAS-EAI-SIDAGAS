@extends('layouts.pelanggan')

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="mb-6 flex justify-between items-end">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Lacak Pesanan</h1>
            <p class="text-gray-500 mt-1">Pantau status pesanan Anda secara real-time.</p>
        </div>
        <button onclick="loadTracking()" class="text-blue-600 hover:text-blue-800 text-sm font-semibold flex items-center gap-1 bg-blue-50 px-3 py-1.5 rounded-full transition">
            <i class="fa-solid fa-rotate-right"></i> Muat Ulang
        </button>
    </div>

    <div id="trackingContainer" class="space-y-6">
        <div class="text-center py-10">
            <i class="fa-solid fa-circle-notch fa-spin text-3xl text-blue-500 mb-3"></i>
            <p class="text-gray-500">Mengambil data dari server...</p>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const API_GATEWAY_URL = 'http://localhost:3000';
    const customerName = "{{ session('name') }}"; // Ambil nama dari session PHP

    async function loadTracking() {
        const container = document.getElementById('trackingContainer');
        try {
            const res = await axios.post(`${API_GATEWAY_URL}/order`, {
                query: `query { getOrders { id customer_name item_name quantity status created_at } }`
            });
            
            const allOrders = res.data.data.getOrders || [];
            // Filter pesanan milik user ini saja (atau ambil semua jika session nama tidak ada untuk contoh)
            const myOrders = customerName ? allOrders.filter(o => o.customer_name === customerName) : allOrders;
            
            if (myOrders.length === 0) {
                container.innerHTML = `
                    <div class="bg-white rounded-2xl border border-gray-100 p-10 text-center shadow-sm">
                        <i class="fa-solid fa-box-open text-5xl text-gray-300 mb-4"></i>
                        <h3 class="text-lg font-bold text-gray-800">Belum Ada Pesanan</h3>
                        <p class="text-gray-500 mb-6">Anda belum pernah melakukan pemesanan.</p>
                        <a href="/pelanggan" class="bg-blue-600 text-white font-bold py-2 px-6 rounded-lg">Mulai Belanja</a>
                    </div>
                `;
                return;
            }

            let html = '';
            // Sort by ID descending (newest first)
            myOrders.sort((a,b) => b.id - a.id).forEach(order => {
                // Tentukan step timeline (1 sampai 4)
                let step = 1;
                let statusText = "Menunggu Diproses";
                
                if(order.status === 'pending') { step = 1; }
                else if(order.status === 'ready_for_delivery') { step = 2; statusText = "Siap Dikirim (Gudang)"; }
                else if(order.status === 'on_delivery' || order.status === 'shipping') { step = 3; statusText = "Dalam Perjalanan (Driver)"; }
                else if(order.status === 'completed') { step = 4; statusText = "Selesai & Diterima"; }

                html += `
                    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden hover:shadow-md transition">
                        <div class="p-5 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
                            <div>
                                <span class="bg-blue-100 text-blue-700 text-xs font-bold px-2 py-1 rounded">ORD-${order.id}</span>
                                <span class="text-xs text-gray-500 ml-2"><i class="fa-regular fa-clock"></i> ${new Date(order.created_at).toLocaleString('id-ID')}</span>
                            </div>
                            <span class="text-sm font-bold text-gray-800">${statusText}</span>
                        </div>
                        <div class="p-5 flex items-center gap-4 border-b border-gray-100">
                            <div class="w-12 h-12 bg-blue-50 rounded-lg flex items-center justify-center text-blue-500 text-xl flex-shrink-0">
                                <i class="fa-solid ${order.item_name.toLowerCase().includes('gas') ? 'fa-fire-burner' : 'fa-bottle-water'}"></i>
                            </div>
                            <div>
                                <h4 class="font-bold text-gray-800">${order.item_name}</h4>
                                <p class="text-sm text-gray-500">${order.quantity} x Barang</p>
                            </div>
                        </div>
                        
                        <!-- Visual Timeline -->
                        <div class="p-5 bg-white">
                            <div class="flex items-center justify-between relative">
                                <!-- Connecting Lines Background -->
                                <div class="absolute top-1/2 left-4 right-4 h-1 bg-gray-200 -z-10 -translate-y-1/2 rounded"></div>
                                <!-- Connecting Lines Active -->
                                <div class="absolute top-1/2 left-4 h-1 bg-green-500 -z-10 -translate-y-1/2 rounded transition-all duration-1000" style="width: ${(step-1) * 33.33}%"></div>

                                <!-- Step 1 -->
                                <div class="flex flex-col items-center">
                                    <div class="w-8 h-8 rounded-full flex items-center justify-center font-bold text-sm shadow-sm ${step >= 1 ? 'bg-green-500 text-white' : 'bg-gray-200 text-gray-400'}"><i class="fa-solid fa-receipt"></i></div>
                                    <span class="text-[10px] font-bold text-gray-500 mt-2 uppercase text-center w-16">Dibuat</span>
                                </div>
                                <!-- Step 2 -->
                                <div class="flex flex-col items-center">
                                    <div class="w-8 h-8 rounded-full flex items-center justify-center font-bold text-sm shadow-sm ${step >= 2 ? 'bg-green-500 text-white' : 'bg-gray-200 text-gray-400'}"><i class="fa-solid fa-box"></i></div>
                                    <span class="text-[10px] font-bold text-gray-500 mt-2 uppercase text-center w-16">Siap Kirim</span>
                                </div>
                                <!-- Step 3 -->
                                <div class="flex flex-col items-center">
                                    <div class="w-8 h-8 rounded-full flex items-center justify-center font-bold text-sm shadow-sm ${step >= 3 ? 'bg-green-500 text-white' : 'bg-gray-200 text-gray-400'}"><i class="fa-solid fa-truck"></i></div>
                                    <span class="text-[10px] font-bold text-gray-500 mt-2 uppercase text-center w-16">Di Jalan</span>
                                </div>
                                <!-- Step 4 -->
                                <div class="flex flex-col items-center">
                                    <div class="w-8 h-8 rounded-full flex items-center justify-center font-bold text-sm shadow-sm ${step >= 4 ? 'bg-green-500 text-white' : 'bg-gray-200 text-gray-400'}"><i class="fa-solid fa-house-chimney-user"></i></div>
                                    <span class="text-[10px] font-bold text-gray-500 mt-2 uppercase text-center w-16">Selesai</span>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
            });
            container.innerHTML = html;

        } catch (err) {
            console.error(err);
            container.innerHTML = '<div class="bg-red-50 text-red-500 p-4 rounded-xl text-center border border-red-100">Gagal menghubungkan ke server pelacakan.</div>';
        }
    }

    document.addEventListener('DOMContentLoaded', loadTracking);
</script>
@endpush
