<header class="bg-white/10 backdrop-blur-md border-b border-white/20">
    <div class="container mx-auto px-4 py-6">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <i class="fas fa-chart-line text-3xl text-white"></i>
                <div>
                    <h1 class="text-2xl font-bold text-white">Government Procurement Dashboard</h1>
                    <p class="text-white/80 text-sm">Transparency in public spending</p>
                </div>
            </div>
            
            <nav class="hidden md:flex space-x-6">
                <a href="{{ url('/') }}" class="text-white/90 hover:text-white transition-colors duration-200 flex items-center space-x-2">
                    <i class="fas fa-home"></i>
                    <span>Dashboard</span>
                </a>
                <a href="#" class="text-white/90 hover:text-white transition-colors duration-200 flex items-center space-x-2">
                    <i class="fas fa-chart-bar"></i>
                    <span>Analytics</span>
                </a>
                <a href="#" class="text-white/90 hover:text-white transition-colors duration-200 flex items-center space-x-2">
                    <i class="fas fa-info-circle"></i>
                    <span>About</span>
                </a>
            </nav>
            
            <!-- Mobile menu button -->
            <button class="md:hidden text-white" onclick="toggleMobileMenu()">
                <i class="fas fa-bars text-xl"></i>
            </button>
        </div>
        
        <!-- Mobile menu -->
        <div id="mobile-menu" class="hidden md:hidden mt-4 space-y-2">
            <a href="{{ url('/') }}" class="block text-white/90 hover:text-white transition-colors duration-200 py-2">
                <i class="fas fa-home mr-2"></i>Dashboard
            </a>
            <a href="#" class="block text-white/90 hover:text-white transition-colors duration-200 py-2">
                <i class="fas fa-chart-bar mr-2"></i>Analytics
            </a>
            <a href="#" class="block text-white/90 hover:text-white transition-colors duration-200 py-2">
                <i class="fas fa-info-circle mr-2"></i>About
            </a>
        </div>
    </div>
</header>

<script>
function toggleMobileMenu() {
    const menu = document.getElementById('mobile-menu');
    menu.classList.toggle('hidden');
}
</script>