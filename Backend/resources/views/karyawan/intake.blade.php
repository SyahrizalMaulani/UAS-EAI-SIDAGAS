@extends('layouts.karyawan')

@section('content')
<div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
    <div class="px-6 py-5 border-b border-gray-200 bg-gray-50 flex justify-between items-center">
        <div>
            <h2 class="text-xl font-bold text-gray-800">Penerimaan Galon Kosong</h2>
            <p class="text-sm text-gray-500 mt-1">Catat galon kosong yang dikembalikan oleh pelanggan/driver.</p>
        </div>
        <button onclick="showIntakeForm()" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg font-medium shadow-sm transition flex items-center gap-2">
            <i class="fa-solid fa-plus"></i> Catat Masuk
        </button>
    </div>

    <div class="p-6">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-100 text-gray-600 text-xs uppercase tracking-wider">
                        <th class="px-4 py-3 font-semibold rounded-tl-lg">Waktu Masuk</th>
                        <th class="px-4 py-3 font-semibold">Driver/Sumber</th>
                        <th class="px-4 py-3 font-semibold">Jumlah (Qty)</th>
                        <th class="px-4 py-3 font-semibold">Kondisi</th>
                        <th class="px-4 py-3 font-semibold text-right rounded-tr-lg">Aksi</th>
                    </tr>
                </thead>
                <tbody id="intakeTable" class="divide-y divide-gray-100 text-sm text-gray-700">
                    <!-- Data dummy awal, di sistem nyata di-fetch dari API -->
                    <tr class="hover:bg-slate-50 transition">
                        <td class="px-4 py-4">Hari ini, 08:30</td>
                        <td class="px-4 py-4 font-medium">Budi (Driver)</td>
                        <td class="px-4 py-4"><span class="bg-blue-100 text-blue-800 font-bold px-2 py-1 rounded">50</span></td>
                        <td class="px-4 py-4"><span class="text-green-600 font-medium"><i class="fa-solid fa-check"></i> Baik</span></td>
                        <td class="px-4 py-4 text-right">
                            <button class="text-indigo-600 hover:text-indigo-900 font-medium text-xs bg-indigo-50 px-3 py-1.5 rounded transition">Pindahkan ke Antrean Cuci</button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function showIntakeForm() {
        Swal.fire({
            title: 'Catat Galon Kosong Masuk',
            html: `
                <input id="swal-qty" type="number" class="swal2-input" placeholder="Jumlah Galon">
                <select id="swal-driver" class="swal2-select">
                    <option value="" disabled selected>Pilih Driver</option>
                    <option value="Budi">Budi</option>
                    <option value="Joko">Joko</option>
                    <option value="Pelanggan Langsung">Pelanggan Langsung</option>
                </select>
            `,
            focusConfirm: false,
            showCancelButton: true,
            confirmButtonText: 'Simpan Data',
            preConfirm: () => {
                const qty = document.getElementById('swal-qty').value;
                const driver = document.getElementById('swal-driver').value;
                if (!qty || !driver) {
                    Swal.showValidationMessage(`Harap isi jumlah dan pilih sumber!`);
                }
                return { qty: qty, driver: driver }
            }
        }).then((result) => {
            if (result.isConfirmed) {
                // Di sini letak GraphQL Mutation untuk mencatat Intake
                Swal.fire('Berhasil!', `${result.value.qty} galon dari ${result.value.driver} telah dicatat.`, 'success');
                
                // Tambah baris ke tabel secara dinamis (tanpa reload)
                const tbody = document.getElementById('intakeTable');
                const tr = document.createElement('tr');
                tr.className = 'hover:bg-slate-50 transition bg-green-50';
                tr.innerHTML = `
                    <td class="px-4 py-4">Baru Saja</td>
                    <td class="px-4 py-4 font-medium">${result.value.driver}</td>
                    <td class="px-4 py-4"><span class="bg-blue-100 text-blue-800 font-bold px-2 py-1 rounded">${result.value.qty}</span></td>
                    <td class="px-4 py-4"><span class="text-green-600 font-medium"><i class="fa-solid fa-check"></i> Baik</span></td>
                    <td class="px-4 py-4 text-right">
                        <button class="text-indigo-600 hover:text-indigo-900 font-medium text-xs bg-indigo-50 px-3 py-1.5 rounded transition">Pindahkan ke Antrean Cuci</button>
                    </td>
                `;
                tbody.prepend(tr);
            }
        });
    }
</script>
@endpush
