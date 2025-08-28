<header class="bg-white/10 backdrop-blur-md border-b border-white/20">
    <div class="container mx-auto px-4 py-6">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <i class="fas fa-chart-line text-3xl text-white"></i>
                <div>
                    <h1 class="text-2xl font-bold text-white">{{ __('app.site_title') }}</h1>
                    <p class="text-white/80 text-sm">{{ __('app.site_description') }}</p>
                </div>
            </div>

            <div class="flex items-center space-x-6">
                <nav class="hidden md:flex space-x-6">
                    <a href="{{ url('/') }}" class="text-white/90 hover:text-white transition-colors duration-200 flex items-center space-x-2">
                        <i class="fas fa-home"></i>
                        <span>{{ __('app.dashboard') }}</span>
                    </a>
                    <a href="{{ route('organizations.index') }}" class="text-white/90 hover:text-white transition-colors duration-200 flex items-center space-x-2">
                        <i class="fas fa-building-columns"></i>
                        <span>{{ __('app.organizations') }}</span>
                    </a>
                </nav>
                
                <!-- Language Switcher -->
                @php
                    $languages = \App\Helpers\LanguageHelper::getSupportedLanguages();
                    $currentLang = \App\Helpers\LanguageHelper::getCurrentLanguage();
                @endphp
                <div class="relative">
                    <button onclick="toggleLanguageDropdown()" class="text-white/90 hover:text-white transition-colors duration-200 flex items-center space-x-2">
                        <i class="fas fa-globe"></i>
                        <span class="hidden md:inline">{{ $currentLang['native'] }}</span>
                        <i class="fas fa-chevron-down text-xs"></i>
                    </button>
                    <div id="language-dropdown" class="absolute right-0 mt-2 w-40 bg-white rounded-md shadow-lg opacity-0 invisible transition-all duration-300 z-50">
                        <div class="py-1">
                            @foreach($languages as $locale => $language)
                                <a href="{{ route('language.switch', $locale) }}" 
                                   class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-colors @if(app()->getLocale() === $locale) bg-gray-50 font-semibold @endif">
                                    {{ $language['native'] }}
                                </a>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <!-- Mobile menu button -->
            <button class="md:hidden text-white" onclick="toggleMobileMenu()">
                <i class="fas fa-bars text-xl"></i>
            </button>
        </div>

        <!-- Mobile menu -->
        <div id="mobile-menu" class="hidden md:hidden mt-4 space-y-2">
            <a href="{{ url('/') }}" class="block text-white/90 hover:text-white transition-colors duration-200 py-2">
                <i class="fas fa-home mr-2"></i>{{ __('app.dashboard') }}
            </a>
            <a href="{{ route('organizations.index') }}" class="block text-white/90 hover:text-white transition-colors duration-200 py-2">
                <i class="fas fa-building-columns mr-2"></i>{{ __('app.organizations') }}
            </a>
            <div class="border-t border-white/20 pt-2 mt-2">
                <p class="text-white/70 text-xs mb-2">{{ __('app.switch_language') }}:</p>
                @foreach($languages as $locale => $language)
                    <a href="{{ route('language.switch', $locale) }}" 
                       class="block text-white/90 hover:text-white transition-colors duration-200 py-1 ml-2 @if(app()->getLocale() === $locale) font-semibold @endif">
                        {{ $language['native'] }}
                    </a>
                @endforeach
            </div>
        </div>
    </div>
</header>

<script>
function toggleMobileMenu() {
    const menu = document.getElementById('mobile-menu');
    menu.classList.toggle('hidden');
}

function toggleLanguageDropdown() {
    const dropdown = document.getElementById('language-dropdown');
    dropdown.classList.toggle('opacity-0');
    dropdown.classList.toggle('invisible');
}

// Close language dropdown when clicking outside
document.addEventListener('click', function(event) {
    const dropdown = document.getElementById('language-dropdown');
    const button = event.target.closest('button[onclick="toggleLanguageDropdown()"]');
    
    if (!button && !dropdown.contains(event.target)) {
        dropdown.classList.add('opacity-0');
        dropdown.classList.add('invisible');
    }
});
</script>
