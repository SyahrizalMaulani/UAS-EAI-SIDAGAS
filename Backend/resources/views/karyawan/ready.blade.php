@extends('layouts.karyawan')

@section('content')
<div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
    <div class="p-6 border-b border-gray-200 bg-gray-50">
        <h2 class="text-xl font-bold text-gray-800"><i class="fa-solid fa-warehouse text-green-500 mr-2"></i>Gudang Barang Siap Kirim</h2>
        <p class="text-sm text-gray-500 mt-1">Ringkasan stok riil yang saat ini siap diangkut oleh Driver berdasarkan pesanan.</p>
    </div>

    <div class="p-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            
            <!-- Summary Card -->
            <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-xl p-6 text-white shadow-md">
                <h3 class="text-green-100 font-medium text-sm uppercase tracking-wider mb-2">Total Galon Siap Angkut</h3>
                <div class="flex items-end gap-3">
                    <span class="text-5xl font-bold" id="totalReadyStock">0</span>
                    <span class="text-xl font-medium mb-1">Galon</span>
                </div>
                <div class="mt-6 pt-4 border-t border-green-400/50 flex justify-between items-center text-sm">
                    <span>Diperbarui secara real-time dari Inventory Service</span>
                    <i class="fa-solid fa-satellite-dish animate-pulse"></i>
                </div>
            </div>

            <!-- Warning Card -->
            <div class="bg-slate-50 border border-slate-200 rounded-xl p-6 flex flex-col justify-center">
                <div class="flex items-start gap-4">
                    <div class="w-12 h-12 rounded-full bg-orange-100 text-orange-500 flex items-center justify-center text-xl flex-shrink-0">
                        <i class="fa-solid fa-truck-clock"></i>
                    </div>
                    <div>
                        <h4 class="font-bold text-gray-800 text-lg mb-1">Driver Menunggu Muatan</h4>
                        <p class="text-sm text-gray-600 mb-3">Terdapat 3 pesanan berstatus <span class="font-semibold text-blue-600">order.ready</span> yang belum dimuat ke truk driver.</p>
                        <button class="bg-slate-800 hover:bg-slate-900 text-white text-sm px-4 py-2 rounded-lg transition font-medium">Cetak Surat Jalan</button>
                    </div>
                </div>
            </div>

        </div>

        <h3 class="font-bold text-gray-800 mt-8 mb-4">Daftar Stok Terkini (API Gateway)</h3>
        <div id="stockList" class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="text-center py-8 col-span-full">
                <i class="fa-solid fa-circle-notch fa-spin text-3xl text-indigo-500 mb-2"></i>
                <p class="text-gray-500">Menyinkronkan data...</p>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const API_GATEWAY_URL = 'http://localhost:3000';

    async function loadReadyStock() {
        try {
            const invRes = await axios.post(`${API_GATEWAY_URL}/inventory`, {
                query: `query { getInventory { item_name stock } }`
            });
            const inventory = invRes.data.data.getInventory || [];
            
            let totalGalon = 0;
            let listHtml = '';

            inventory.forEach(item => {
                if(item.item_name.toLowerCase().includes('galon')) {
                    totalGalon += item.stock;
                }
                
                listHtml += `
                    <div class="border border-gray-200 rounded-lg p-4 text-center hover:border-indigo-400 hover:shadow-sm transition bg-white">
                        <p class="text-xs text-gray-500 font-medium mb-1">${item.item_name}</p>
                        <p class="text-2xl font-bold text-gray-800">${item.stock}</p>
                    </div>
                `;
            });

            // Animasi angka
            animateValue("totalReadyStock", 0, totalGalon, 1000);
            
            document.getElementById('stockList').innerHTML = listHtml;

        } catch (err) {
            console.error(err);
            document.getElementById('stockList').innerHTML = '<p class="col-span-full text-center text-red-500">Gagal memuat data dari server.</p>';
        }
    }

    function animateValue(id, start, end, duration) {
        if (start === end) return;
        var range = end - start;
        var current = start;
        var increment = end > start? 1 : -1;
        var stepTime = Math.abs(Math.floor(duration / range));
        var obj = document.getElementById(id);
        var timer = setInterval(function() {
            current += increment;
            obj.innerHTML = current;
            if (current == end) {
                clearInterval(timer);
            }
        }, stepTime);
    }

    document.addEventListener('DOMContentLoaded', loadReadyStock);
</script>
@endpush
