@extends('layouts.driver')

@section('content')
<div class="mb-4 flex justify-between items-end">
    <div>
        <h2 class="text-2xl font-bold text-gray-800 tracking-tight">Tugas Hari Ini</h2>
        <p class="text-sm text-gray-500">Daftar pesanan yang siap dikirim.</p>
    </div>
    <div class="bg-blue-100 text-blue-700 px-3 py-1 rounded-full text-sm font-bold shadow-sm" id="taskCounter">
        0 Tugas
    </div>
</div>

<div id="deliveriesList" class="space-y-4">
    <!-- Loading State -->
    <div class="text-center py-10">
        <i class="fa-solid fa-circle-notch fa-spin text-4xl text-blue-500 mb-3"></i>
        <p class="text-gray-500 font-medium">Mencari rute pengiriman...</p>
    </div>
</div>

<!-- Template for empty state (hidden by default) -->
<div id="emptyState" class="hidden text-center py-12 px-4">
    <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
        <i class="fa-solid fa-mug-hot text-3xl text-gray-400"></i>
    </div>
    <h3 class="text-lg font-bold text-gray-800 mb-1">Semua Selesai!</h3>
    <p class="text-sm text-gray-500">Belum ada tugas pengiriman baru untuk saat ini. Waktunya istirahat sejenak.</p>
</div>
@endsection

@push('scripts')
<script>
    const API_GATEWAY_URL = 'http://localhost:3000';

    async function loadDeliveries() {
        try {
            const res = await axios.post(`${API_GATEWAY_URL}/delivery`, {
                query: `query { getDeliveries { id order_id customer_name status } }`
            });
            const deliveries = res.data.data.getDeliveries || [];
            
            // Filter hanya yang scheduled dan urutkan berdasarkan yang paling lama (ID terkecil)
            const pendingTasks = deliveries.filter(d => d.status === 'scheduled').sort((a,b) => a.id - b.id);
            document.getElementById('taskCounter').innerText = `${pendingTasks.length} Tugas`;

            const listContainer = document.getElementById('deliveriesList');
            
            if (pendingTasks.length === 0) {
                listContainer.innerHTML = '';
                document.getElementById('emptyState').classList.remove('hidden');
                return;
            }

            let html = '';
            pendingTasks.forEach(d => {
                // Dummy data untuk alamat & telepon karena belum ada di skema asli
                const dummyAddress = `Jl. Telekomunikasi No. ${Math.floor(Math.random()*100)}, Bojongsoang`;
                const dummyPhone = `0812-3456-${Math.floor(Math.random()*9000)+1000}`;
                const dummyQty = Math.floor(Math.random() * 5) + 1;

                html += `
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 relative overflow-hidden">
                        <!-- Accent line -->
                        <div class="absolute top-0 left-0 w-1 h-full bg-blue-500"></div>
                        
                        <div class="flex justify-between items-start mb-3">
                            <div>
                                <span class="bg-blue-50 text-blue-600 text-xs font-bold px-2 py-1 rounded">ORD-${d.order_id}</span>
                                <h3 class="text-xl font-extrabold text-gray-800 mt-2">${d.customer_name}</h3>
                            </div>
                            <div class="text-right">
                                <span class="text-sm text-gray-500 block">Jml Pesanan</span>
                                <span class="text-2xl font-black text-blue-600">${dummyQty} <span class="text-sm">Galon</span></span>
                            </div>
                        </div>

                        <div class="space-y-2 mb-5">
                            <div class="flex items-start gap-3 text-sm text-gray-600">
                                <i class="fa-solid fa-location-dot mt-1 text-gray-400 w-4 text-center"></i>
                                <span>${dummyAddress}</span>
                            </div>
                            <div class="flex items-center gap-3 text-sm text-gray-600">
                                <i class="fa-solid fa-phone text-gray-400 w-4 text-center"></i>
                                <span>${dummyPhone}</span>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <a href="https://maps.google.com/?q=${dummyAddress}" target="_blank" class="bg-gray-100 hover:bg-gray-200 text-gray-700 py-3 rounded-xl font-bold text-sm text-center transition flex justify-center items-center gap-2">
                                <i class="fa-solid fa-map-location-dot"></i> Peta
                            </a>
                            <button onclick="startDelivery(${d.id}, ${d.order_id}, '${d.customer_name}', ${dummyQty})" class="bg-blue-600 hover:bg-blue-700 text-white py-3 rounded-xl font-bold text-sm text-center shadow-lg shadow-blue-500/30 transition flex justify-center items-center gap-2">
                                <i class="fa-solid fa-truck-fast"></i> Ambil
                            </button>
                        </div>
                    </div>
                `;
            });
            listContainer.innerHTML = html;

        } catch (err) {
            console.error(err);
            document.getElementById('deliveriesList').innerHTML = '<div class="bg-red-50 text-red-500 p-4 rounded-xl text-center">Gagal menghubungi server pusat.</div>';
        }
    }

    function startDelivery(deliveryId, orderId, customerName, qty) {
        Swal.fire({
            title: 'Mulai Pengiriman?',
            text: "Anda akan mengunci pesanan ini sebagai tugas Anda.",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#2563eb',
            cancelButtonColor: '#94a3b8',
            confirmButtonText: 'Ya, Ambil Tugas!'
        }).then((result) => {
            if (result.isConfirmed) {
                // Simpan detail tugas ke localStorage
                localStorage.setItem('activeDeliveryId', deliveryId);
                localStorage.setItem('activeOrderId', orderId);
                localStorage.setItem('activeCustomerName', customerName);
                localStorage.setItem('activeQty', qty);
                // Arahkan ke halaman "Berjalan"
                window.location.href = '/driver/active';
            }
        });
    }

    document.addEventListener('DOMContentLoaded', loadDeliveries);
</script>
@endpush
