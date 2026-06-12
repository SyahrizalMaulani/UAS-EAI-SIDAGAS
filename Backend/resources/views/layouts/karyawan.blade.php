<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIDAGAS Produksi - Panel Karyawan</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style> body { font-family: 'Inter', sans-serif; background-color: #f1f5f9; } </style>
</head>
<body class="flex flex-col h-screen">

    <!-- Top Navigation -->
    <nav class="bg-indigo-700 text-white shadow-lg z-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <div class="flex items-center gap-3">
                    <i class="fa-solid fa-industry text-2xl text-indigo-300"></i>
                    <span class="font-bold text-xl tracking-wide">Pabrik SIDAGAS</span>
                </div>
                <div class="flex items-center gap-4">
                    <div class="hidden md:block text-sm font-medium">Shift: Siang</div>
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button class="bg-indigo-800 hover:bg-red-600 px-3 py-1.5 rounded text-sm transition font-medium"><i class="fa-solid fa-power-off mr-1"></i>Keluar</button>
                    </form>
                </div>
            </div>
        </div>
    </nav>

    <!-- Sub Navigation Tabs (Kanban style) -->
    <div class="bg-white border-b border-gray-200 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex space-x-8">
                <a href="/karyawan" class="border-b-2 py-4 px-1 text-sm font-medium transition {{ request()->is('karyawan') ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700' }}">
                    <i class="fa-solid fa-truck-ramp-box mr-2"></i> Intake Galon Kosong
                </a>
                <a href="/karyawan/production" class="border-b-2 py-4 px-1 text-sm font-medium transition {{ request()->is('karyawan/production') ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700' }}">
                    <i class="fa-solid fa-gears mr-2"></i> Proses Produksi
                </a>
                <a href="/karyawan/ready" class="border-b-2 py-4 px-1 text-sm font-medium transition {{ request()->is('karyawan/ready') ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700' }}">
                    <i class="fa-solid fa-check-circle mr-2"></i> Siap Kirim
                </a>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <main class="flex-1 overflow-auto p-4 sm:p-6 lg:p-8">
        <div class="max-w-7xl mx-auto">
            @yield('content')
        </div>
    </main>

    @stack('scripts')
</body>
</html>
