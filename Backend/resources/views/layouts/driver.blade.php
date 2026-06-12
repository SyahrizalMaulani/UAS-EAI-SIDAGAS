<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>SIDAGAS Driver</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style> 
        body { font-family: 'Outfit', sans-serif; background-color: #f8fafc; } 
        /* Menyembunyikan scrollbar untuk pengalaman mobile yang lebih bersih */
        ::-webkit-scrollbar { width: 0px; background: transparent; }
    </style>
</head>
<body class="flex flex-col h-screen overscroll-none">

    <!-- Top Header Mobile -->
    <header class="bg-blue-600 text-white shadow-md z-10 sticky top-0">
        <div class="px-4 py-3 flex justify-between items-center">
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 bg-white/20 rounded-full flex items-center justify-center">
                    <i class="fa-solid fa-truck text-white"></i>
                </div>
                <div>
                    <h1 class="text-lg font-bold leading-tight">Driver SIDAGAS</h1>
                    <p class="text-[10px] text-blue-100 uppercase tracking-widest">Hi, {{ session('name', 'Budi') }}</p>
                </div>
            </div>
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button class="w-8 h-8 rounded-full hover:bg-white/10 flex items-center justify-center transition">
                    <i class="fa-solid fa-right-from-bracket text-lg"></i>
                </button>
            </form>
        </div>
    </header>

    <!-- Main Content Area -->
    <main class="flex-1 overflow-y-auto pb-20 px-4 pt-4">
        @yield('content')
    </main>

    <!-- Bottom Navigation Bar -->
    <nav class="fixed bottom-0 w-full bg-white border-t border-gray-200 flex justify-around items-center pb-safe shadow-[0_-4px_6px_-1px_rgba(0,0,0,0.05)] z-20 h-16">
        
        <a href="/driver" class="flex flex-col items-center justify-center w-full h-full space-y-1 {{ request()->is('driver') ? 'text-blue-600' : 'text-gray-400' }}">
            <i class="fa-solid fa-clipboard-list text-xl"></i>
            <span class="text-[10px] font-bold uppercase tracking-wider">Daftar</span>
        </a>
        
        <a href="/driver/active" class="flex flex-col items-center justify-center w-full h-full space-y-1 relative {{ request()->is('driver/active') ? 'text-blue-600' : 'text-gray-400' }}">
            <div class="{{ request()->is('driver/active') ? 'bg-blue-100 rounded-full w-12 h-8 flex items-center justify-center' : '' }}">
                <i class="fa-solid fa-route text-xl"></i>
            </div>
            <span class="text-[10px] font-bold uppercase tracking-wider">Berjalan</span>
            <!-- Notification Dot -->
            <span class="absolute top-2 right-6 w-2 h-2 bg-red-500 rounded-full"></span>
        </a>
        
        <a href="/driver/history" class="flex flex-col items-center justify-center w-full h-full space-y-1 {{ request()->is('driver/history') ? 'text-blue-600' : 'text-gray-400' }}">
            <i class="fa-solid fa-wallet text-xl"></i>
            <span class="text-[10px] font-bold uppercase tracking-wider">Setoran</span>
        </a>
        
    </nav>

    @stack('scripts')
</body>
</html>
