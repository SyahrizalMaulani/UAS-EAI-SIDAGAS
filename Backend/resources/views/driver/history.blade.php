@extends('layouts.driver')

@section('content')
<h2 class="text-2xl font-bold text-gray-800 tracking-tight mb-6">Ringkasan Setoran</h2>

<!-- Kartu Total -->
<div class="bg-gradient-to-br from-slate-800 to-slate-900 rounded-2xl shadow-xl p-6 text-white mb-6 relative overflow-hidden">
    <i class="fa-solid fa-wallet absolute -right-6 -bottom-6 text-8xl text-white opacity-5 transform rotate-12"></i>
    
    <p class="text-slate-300 font-medium text-sm uppercase tracking-wider mb-2">Total Setoran Hari Ini</p>
    <h3 class="text-4xl font-black mb-1 text-green-400" id="totalSetoran">Rp ...</h3>
    <p class="text-sm text-slate-400" id="totalCount">Menghitung...</p>
    
    <div class="mt-6 pt-4 border-t border-slate-700 flex justify-between items-center">
        <span class="text-xs bg-slate-700 px-2 py-1 rounded text-slate-300">Target Harian: 10</span>
        <button class="text-sm font-bold text-blue-400 hover:text-blue-300 transition">Setor ke Keuangan <i class="fa-solid fa-chevron-right ml-1 text-xs"></i></button>
    </div>
</div>

<h3 class="font-bold text-gray-800 mb-3 text-sm uppercase tracking-wider text-gray-400">Riwayat Terakhir</h3>

<div id="historyList" class="space-y-3">
    <div class="text-center py-6">
        <i class="fa-solid fa-spinner fa-spin text-2xl text-blue-500 mb-2"></i>
        <p class="text-sm text-gray-500">Memuat riwayat...</p>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const API_GATEWAY_URL = 'http://localhost:3000';

    async function loadHistory() {
        try {
            const res = await axios.post(`${API_GATEWAY_URL}/delivery`, {
                query: `query { getDeliveries { id order_id customer_name status } }`
            });
            const deliveries = res.data.data.getDeliveries || [];
            
            const completed = deliveries.filter(d => d.status === 'completed').sort((a,b) => b.id - a.id);
            const container = document.getElementById('historyList');
            
            if (completed.length === 0) {
                container.innerHTML = '<p class="text-sm text-gray-500 text-center py-4 bg-white rounded-lg border border-gray-100">Belum ada pengiriman selesai.</p>';
                document.getElementById('totalSetoran').innerText = 'Rp 0';
                document.getElementById('totalCount').innerText = 'Belum ada pengiriman';
                return;
            }

            let html = '';
            let total = 0;

            completed.forEach(d => {
                // Dummy estimasi harga untuk display
                const dummyPrice = 20000 * (Math.floor(Math.random() * 3) + 1);
                total += dummyPrice;

                html += `
                    <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100 flex items-center justify-between">
                        <div class="flex items-center gap-4">
                            <div class="w-10 h-10 rounded-full bg-green-100 text-green-600 flex items-center justify-center flex-shrink-0">
                                <i class="fa-solid fa-check"></i>
                            </div>
                            <div>
                                <h4 class="font-bold text-gray-800">${d.customer_name}</h4>
                                <p class="text-xs text-gray-500">ORD-${d.order_id} • Tunai</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <span class="font-black text-gray-800">Rp ${dummyPrice.toLocaleString('id-ID')}</span>
                        </div>
                    </div>
                `;
            });
            
            container.innerHTML = html;
            document.getElementById('totalSetoran').innerText = 'Rp ' + total.toLocaleString('id-ID');
            document.getElementById('totalCount').innerText = `Dari ${completed.length} Pengiriman Selesai`;

        } catch (error) {
            console.error(error);
            document.getElementById('historyList').innerHTML = '<p class="text-sm text-red-500 text-center py-4">Gagal memuat riwayat dari server.</p>';
        }
    }

    document.addEventListener('DOMContentLoaded', loadHistory);
</script>
@endpush
