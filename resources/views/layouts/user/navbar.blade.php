<!-- Brand -->
<div class="navbar-brand">
    <img src="{{ asset('images/logo-vokasi-ub.png') }}" alt="Logo UB">
    <span>Lab IoT Vokasi</span>
</div>

<!-- Spacer untuk mendorong navigasi ke ujung kanan -->
<div class="flex-grow"></div>

<!-- Desktop Navigation dan Profile -->
<div class="navbar-nav hidden md:flex items-center">
    <a href="{{ route('user.dashboard-user') }}" 
       class="nav-link px-2 {{ request()->routeIs('user.dashboard-user') ? 'active' : '' }}">
        Dashboard
    </a>
    <a href="{{ route('user.peminjaman') }}" 
       class="nav-link px-2 {{ request()->routeIs('user.peminjaman') ? 'active' : '' }}">
        Daftar Barang
    </a>
    <a href="{{ route('user.status-peminjaman') }}" 
       class="nav-link px-2 {{ request()->routeIs('user.status-peminjaman') ? 'active' : '' }}">
        Peminjaman
    </a>
    <a href="{{ route('user.riwayat-peminjaman') }}" 
       class="nav-link px-2 {{ request()->routeIs('user.riwayat-peminjaman') ? 'active' : '' }}">
        Riwayat
    </a>

    <!-- Profile Dropdown (Desktop) -->
    <div class="navbar-profile relative ml-3">
        <button @click="profileOpen = !profileOpen" class="focus:outline-none flex items-center">
            <div class="w-8 h-8 rounded-full bg-white bg-opacity-20 flex items-center justify-center overflow-hidden">
                @if(auth()->user()->profile_photo_path)
                    <img src="{{ asset('storage/' . auth()->user()->profile_photo_path) }}" alt="Profile" class="w-full h-full object-cover">
                @else
                    <i data-lucide="user" class="w-5 h-5 text-white"></i>
                @endif
            </div>
            <i data-lucide="chevron-down" class="w-4 h-4 ml-1 text-white"></i>
        </button>
        
        <div x-show="profileOpen" 
             @click.away="profileOpen = false"
             class="profile-dropdown absolute right-0 top-full mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-50"
             x-cloak
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95">
            
            <div class="px-4 py-2 border-b border-gray-100">
                <p class="text-sm font-medium text-gray-800">{{ auth()->user()->name }}</p>
                <p class="text-xs text-gray-500 truncate">{{ auth()->user()->email }}</p>
            </div>
            
            <a href="{{ route('user.profile') }}" 
               class="profile-dropdown-item flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                <i data-lucide="user" class="w-4 h-4 mr-2 text-gray-500"></i>
                Profile
            </a>
            
            <button onclick="confirmLogout()" class="w-full text-left profile-dropdown-item flex items-center px-4 py-2 text-sm text-red-600 hover:bg-gray-100">
                    <i data-lucide="log-out" class="w-4 h-4 mr-2 text-red-500"></i>
                    Logout
                </button>
        </div>
    </div>
</div>

<!-- Mobile Menu Button -->
<button @click="mobileOpen = !mobileOpen" 
    class="md:hidden focus:outline-none mr-4">
<i data-lucide="menu" class="h-6 w-6 text-white"></i>
</button>

<!-- Mobile Menu -->
<div x-show="mobileOpen"
     @click.away="mobileOpen = false"
     class="mobile-menu absolute top-full left-0 right-0 bg-[#0F4C81] shadow-lg z-40"
     x-cloak
     x-transition:enter="transition ease-out duration-200"
     x-transition:enter-start="opacity-0 transform -translate-y-4"
     x-transition:enter-end="opacity-100 transform translate-y-0"
     x-transition:leave="transition ease-in duration-150"
     x-transition:leave-start="opacity-100 transform translate-y-0"
     x-transition:leave-end="opacity-0 transform -translate-y-4">
    
    <div class="py-3 px-4 border-b border-blue-700 flex items-center">
        <div class="w-8 h-8 rounded-full bg-white bg-opacity-20 flex items-center justify-center mr-3 overflow-hidden">
            @if(auth()->user()->profile_photo_path)
                <img src="{{ asset('storage/' . auth()->user()->profile_photo_path) }}" alt="Profile" class="w-full h-full object-cover">
            @else
                <i data-lucide="user" class="w-5 h-5 text-white"></i>
            @endif
        </div>
        <div>
            <p class="text-sm font-medium text-white">{{ auth()->user()->name }}</p>
            <p class="text-xs text-gray-300 truncate">{{ auth()->user()->email }}</p>
        </div>
    </div>
    
    <a href="{{ route('user.dashboard-user') }}" 
       class="mobile-menu-item {{ request()->routeIs('user.dashboard-user') ? 'active' : '' }}">
        Dashboard
    </a>
    <a href="{{ route('user.peminjaman') }}" 
       class="mobile-menu-item {{ request()->routeIs('user.peminjaman') ? 'active' : '' }}">
        Daftar Barang
    </a>
    <a href="{{ route('user.status-peminjaman') }}" 
       class="mobile-menu-item {{ request()->routeIs('user.status-peminjaman') ? 'active' : '' }}">
        Status
    </a>
    <a href="{{ route('user.riwayat-peminjaman') }}" 
       class="mobile-menu-item {{ request()->routeIs('user.riwayat-peminjaman') ? 'active' : '' }}">
        Riwayat
    </a>
    <a href="{{ route('user.profile') }}" 
       class="mobile-menu-item {{ request()->routeIs('user.profile') ? 'active' : '' }}">
        Profile
    </a>
    
    <div class="px-4 py-3">
        <button onclick="confirmLogout()" class="w-full flex justify-center text-white bg-red-600 py-2 px-4 rounded-md hover:bg-red-700">
            Logout
        </button>
    </div>
</div>