<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIDAGAS - Belanja Mudah</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style> body { font-family: 'Inter', sans-serif; background-color: #f9fafb; } </style>
</head>
<body class="flex flex-col min-h-screen">

    <!-- Top Navigation (E-Commerce Style, Matching App Layout) -->
    <nav class="bg-blue-600 shadow-md sticky top-0 z-30">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center gap-2">
                    <a href="/pelanggan" class="-m-1.5 p-1.5 flex items-center gap-2">
                        <img src="{{ asset('img/Sponsor.png') }}" alt="SIDAGAS Logo" class="h-8 object-contain">
                        <h1 class="text-3xl font-bold tracking-tight text-white">SIDAGAS</h1>
                    </a>
                </div>
                
                <div class="hidden md:flex space-x-4 ml-10">
                    <a href="/pelanggan" class="{{ request()->is('pelanggan') ? 'bg-blue-700 text-white' : 'text-white hover:bg-blue-500' }} px-3 py-2 rounded-md text-sm font-medium transition">Katalog</a>
                    <a href="/pelanggan/tracking" class="{{ request()->is('pelanggan/tracking') ? 'bg-blue-700 text-white' : 'text-white hover:bg-blue-500' }} px-3 py-2 rounded-md text-sm font-medium transition">Lacak Pesanan</a>
                </div>

                <div class="flex items-center gap-4">
                    <a href="/pelanggan/checkout" class="relative text-white hover:text-gray-200 transition">
                        <i class="fa-solid fa-cart-shopping text-xl"></i>
                        <span id="cart-badge" class="absolute -top-2 -right-2 bg-red-500 text-white text-[10px] font-bold px-1.5 py-0.5 rounded-full hidden">0</span>
                    </a>
                    
                    <div class="h-6 w-px bg-blue-400 mx-2"></div>
                    
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button class="text-sm font-medium text-white hover:text-red-200 transition flex items-center gap-1">
                            <i class="fa-solid fa-right-from-bracket"></i> <span class="hidden sm:inline">Logout</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </nav>

    <!-- Mobile Menu Tabs -->
    <div class="md:hidden bg-blue-700 flex justify-around">
        <a href="/pelanggan" class="py-3 text-sm font-medium {{ request()->is('pelanggan') ? 'text-white border-b-2 border-white' : 'text-blue-200' }}">Katalog</a>
        <a href="/pelanggan/tracking" class="py-3 text-sm font-medium {{ request()->is('pelanggan/tracking') ? 'text-white border-b-2 border-white' : 'text-blue-200' }}">Lacak</a>
    </div>

    <!-- Main Content Area -->
    <main class="flex-1 w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="bg-white border-t border-gray-200 mt-auto">
        <div class="max-w-7xl mx-auto px-4 py-6 text-center text-sm text-gray-500">
            &copy; 2026 SIDAGAS Microservices. Tugas EAI.
        </div>
    </footer>

    @stack('scripts')
</body>
</html>
