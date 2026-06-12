@extends('layouts.karyawan')

@section('content')
<div class="mb-6 flex justify-between items-end">
    <div>
        <h2 class="text-2xl font-bold text-gray-800">Alur Kerja Produksi</h2>
        <p class="text-gray-500 mt-1">Pindahkan batch galon ke tahap selanjutnya untuk mengubah status stok.</p>
    </div>
    <div class="text-sm bg-blue-100 text-blue-800 px-4 py-2 rounded-lg font-semibold border border-blue-200">
        <i class="fa-solid fa-droplet"></i> Target Hari Ini: 500 Galon
    </div>
</div>

<!-- Kanban Board -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 h-full pb-8">
    
    <!-- Kolom 1: Proses Cuci -->
    <div class="bg-gray-100 rounded-xl border border-gray-200 p-4 flex flex-col">
        <div class="flex justify-between items-center mb-4 border-b border-gray-200 pb-2">
            <h3 class="font-bold text-gray-700 uppercase tracking-wider text-sm"><i class="fa-solid fa-sink text-blue-500 mr-2"></i>Antrean Cuci</h3>
            <span class="bg-gray-200 text-gray-600 px-2 py-1 rounded text-xs font-bold" id="count-cuci">2</span>
        </div>
        
        <div class="flex-1 space-y-3" id="col-cuci">
            <!-- Kanban Card -->
            <div class="bg-white p-4 rounded-lg shadow-sm border-l-4 border-blue-500" id="batch-1">
                <div class="flex justify-between items-start mb-2">
                    <span class="text-xs font-bold text-gray-400">BATCH #1001</span>
                    <span class="text-xs font-bold bg-blue-50 text-blue-600 px-2 rounded">50 Qty</span>
                </div>
                <p class="text-sm text-gray-800 font-medium mb-4">Galon Kosong dari Intake Pagi</p>
                <button onclick="moveBatch('batch-1', 'col-isi')" class="w-full bg-blue-50 hover:bg-blue-100 text-blue-700 text-xs font-bold py-2 rounded transition">
                    Tandai Selesai Dicuci <i class="fa-solid fa-arrow-right ml-1"></i>
                </button>
            </div>

            <!-- Kanban Card -->
            <div class="bg-white p-4 rounded-lg shadow-sm border-l-4 border-blue-500" id="batch-2">
                <div class="flex justify-between items-start mb-2">
                    <span class="text-xs font-bold text-gray-400">BATCH #1002</span>
                    <span class="text-xs font-bold bg-blue-50 text-blue-600 px-2 rounded">100 Qty</span>
                </div>
                <p class="text-sm text-gray-800 font-medium mb-4">Kiriman Gudang Pusat</p>
                <button onclick="moveBatch('batch-2', 'col-isi')" class="w-full bg-blue-50 hover:bg-blue-100 text-blue-700 text-xs font-bold py-2 rounded transition">
                    Tandai Selesai Dicuci <i class="fa-solid fa-arrow-right ml-1"></i>
                </button>
            </div>
        </div>
    </div>

    <!-- Kolom 2: Proses Isi & Segel -->
    <div class="bg-gray-100 rounded-xl border border-gray-200 p-4 flex flex-col">
        <div class="flex justify-between items-center mb-4 border-b border-gray-200 pb-2">
            <h3 class="font-bold text-gray-700 uppercase tracking-wider text-sm"><i class="fa-solid fa-fill-drip text-indigo-500 mr-2"></i>Proses Isi & Segel</h3>
            <span class="bg-gray-200 text-gray-600 px-2 py-1 rounded text-xs font-bold" id="count-isi">0</span>
        </div>
        
        <div class="flex-1 space-y-3 min-h-[150px] border-2 border-dashed border-gray-300 rounded-lg p-2" id="col-isi">
            <!-- Drop zone for cards -->
        </div>
    </div>

    <!-- Kolom 3: Siap Gudang -->
    <div class="bg-gray-100 rounded-xl border border-gray-200 p-4 flex flex-col">
        <div class="flex justify-between items-center mb-4 border-b border-gray-200 pb-2">
            <h3 class="font-bold text-gray-700 uppercase tracking-wider text-sm"><i class="fa-solid fa-box-check text-green-500 mr-2"></i>Masuk Gudang (Siap Kirim)</h3>
            <span class="bg-gray-200 text-gray-600 px-2 py-1 rounded text-xs font-bold" id="count-gudang">0</span>
        </div>
        
        <div class="flex-1 space-y-3 min-h-[150px] border-2 border-dashed border-gray-300 rounded-lg p-2" id="col-gudang">
            <!-- Drop zone for cards -->
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
    const API_GATEWAY_URL = 'http://localhost:3000';

    // Logika UI Sederhana untuk memindahkan kartu antar kolom
    async function moveBatch(cardId, targetColId) {
        const card = document.getElementById(cardId);
        const targetCol = document.getElementById(targetColId);
        const button = card.querySelector('button');
        
        // Disable button during process
        button.disabled = true;
        const originalText = button.innerHTML;
        button.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Memproses...';

        try {
            // SIMULASI AXIOS MUTATION (Contoh: Menambah stok Galon Aqua ke Inventory Service)
            // Di sistem nyata, API Gateway akan meng-handle mutation ini.
            if(targetColId === 'col-gudang') {
                // GraphQL Mutation: Menambah stok saat masuk gudang
                await axios.post(`${API_GATEWAY_URL}/inventory/update`, {
                    // Karena kita tidak mendefinisikan updateInventory mutation di server sebelumnya, ini hanya simulasi hit
                    item_name: 'Galon Aqua', qty: 50
                }).catch(() => {}); // catch error simulasi
            }

            // Animasi pindah kolom
            targetCol.classList.remove('border-dashed', 'border-2', 'border-gray-300', 'p-2');
            targetCol.appendChild(card);

            // Ganti warna card & aksi tombol berdasarkan target kolom
            if (targetColId === 'col-isi') {
                card.classList.replace('border-blue-500', 'border-indigo-500');
                button.className = "w-full bg-indigo-50 hover:bg-indigo-100 text-indigo-700 text-xs font-bold py-2 rounded transition";
                button.innerHTML = 'Segel Selesai, Masukkan Gudang <i class="fa-solid fa-arrow-right ml-1"></i>';
                button.setAttribute('onclick', `moveBatch('${cardId}', 'col-gudang')`);
            } else if (targetColId === 'col-gudang') {
                card.classList.replace('border-indigo-500', 'border-green-500');
                button.remove(); // Hapus tombol karena sudah selesai
                
                // Tambahkan badge sukses
                const successBadge = document.createElement('div');
                successBadge.className = 'mt-4 w-full bg-green-100 text-green-800 text-center text-xs font-bold py-2 rounded';
                successBadge.innerHTML = '<i class="fa-solid fa-check mr-1"></i> Tersedia di Sistem';
                card.appendChild(successBadge);

                // Notifikasi toast
                const Toast = Swal.mixin({
                    toast: true, position: 'top-end', showConfirmButton: false, timer: 3000, timerProgressBar: true
                });
                Toast.fire({ icon: 'success', title: 'Stok Galon berhasil di-update di sistem.' });
            }

            // Update Counters
            updateCounters();

        } catch (error) {
            Swal.fire('Error', 'Gagal menghubungi server.', 'error');
            button.innerHTML = originalText;
        } finally {
            button.disabled = false;
        }
    }

    function updateCounters() {
        document.getElementById('count-cuci').innerText = document.getElementById('col-cuci').querySelectorAll('.bg-white').length;
        document.getElementById('count-isi').innerText = document.getElementById('col-isi').querySelectorAll('.bg-white').length;
        document.getElementById('count-gudang').innerText = document.getElementById('col-gudang').querySelectorAll('.bg-white').length;
    }
</script>
@endpush
