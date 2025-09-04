<header class="bg-white border-b border-neutral-200 shadow-soft sticky top-0 z-50">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8 py-4">
        <div class="flex items-center justify-between">
            <a href="{{ url('/') }}" class="flex items-center space-x-4 hover:opacity-80 transition-opacity duration-200">
                <div class="flex items-center justify-center w-12 h-12 bg-gradient-to-r from-primary-600 to-secondary-600 rounded-xl shadow-medium relative">
                    <i class="fas fa-chart-line text-xl text-white"></i>
                    <div class="absolute -top-1 -right-1 w-4 h-2.5 rounded-sm bg-gradient-to-r from-red-500 via-white to-red-500 border border-neutral-200 shadow-sm">
                        <div class="absolute inset-y-0 left-0 w-1/3 bg-red-500 rounded-l-sm"></div>
                        <div class="absolute inset-y-0 right-0 w-1/3 bg-red-500 rounded-r-sm"></div>
                        <div class="absolute inset-y-0 left-1/3 right-1/3 bg-white flex items-center justify-center">
                            <i class="fas fa-maple-leaf text-red-500" style="font-size: 6px;"></i>
                        </div>
                    </div>
                </div>
                <div>
                    <h1 class="text-xl sm:text-2xl font-bold font-heading text-neutral-900">{{ __('app.site_title') }}</h1>
                    <p class="text-neutral-600 text-sm hidden sm:block">{{ __('app.site_description') }}</p>
                </div>
            </a>

            <div class="flex items-center space-x-6">
                <nav class="hidden lg:flex space-x-1">
                    <a href="{{ url('/') }}" class="px-4 py-2 rounded-lg text-neutral-700 hover:bg-primary-50 hover:text-primary-700 transition-all duration-200 flex items-center space-x-2 font-medium">
                        <i class="fas fa-home text-sm"></i>
                        <span>{{ __('app.dashboard') }}</span>
                    </a>
                    <a href="{{ route('contracts.index') }}" class="px-4 py-2 rounded-lg text-neutral-700 hover:bg-primary-50 hover:text-primary-700 transition-all duration-200 flex items-center space-x-2 font-medium">
                        <i class="fas fa-file-contract text-sm"></i>
                        <span>{{ __('app.contracts_nav') }}</span>
                    </a>
                    <a href="{{ route('organizations.index') }}" class="px-4 py-2 rounded-lg text-neutral-700 hover:bg-primary-50 hover:text-primary-700 transition-all duration-200 flex items-center space-x-2 font-medium">
                        <i class="fas fa-building-columns text-sm"></i>
                        <span>{{ __('app.organizations') }}</span>
                    </a>
                </nav>

                <!-- Language Switcher -->
                @php
                    $languages = \App\Helpers\LanguageHelper::getSupportedLanguages();
                    $currentLang = \App\Helpers\LanguageHelper::getCurrentLanguage();
                @endphp
                <div class="relative">
                    <button onclick="toggleLanguageDropdown()" class="px-3 py-2 rounded-lg text-neutral-700 hover:bg-primary-50 hover:text-primary-700 transition-all duration-200 flex items-center space-x-2 font-medium">
                        <i class="fas fa-globe text-sm"></i>
                        <span class="hidden md:inline">{{ $currentLang['native'] }}</span>
                        <i class="fas fa-chevron-down text-xs"></i>
                    </button>
                    <div id="language-dropdown" class="absolute right-0 mt-2 w-48 bg-white rounded-xl shadow-strong border border-neutral-200 opacity-0 invisible transition-all duration-300 z-50">
                        <div class="py-2">
                            @foreach($languages as $locale => $language)
                                <a href="{{ route('language.switch', $locale) }}"
                                   class="block px-4 py-3 text-sm text-neutral-700 hover:bg-primary-50 hover:text-primary-700 transition-colors rounded-lg mx-2 @if(app()->getLocale() === $locale) bg-primary-50 text-primary-700 font-semibold @endif">
                                    {{ $language['native'] }}
                                </a>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <!-- Mobile menu button -->
            <button class="lg:hidden text-neutral-700 hover:text-primary-700 p-2 rounded-lg hover:bg-primary-50 transition-all duration-200" onclick="toggleMobileMenu()">
                <i class="fas fa-bars text-lg"></i>
            </button>
        </div>

        <!-- Mobile menu -->
        <div id="mobile-menu" class="hidden lg:hidden mt-6 pt-6 border-t border-neutral-200">
            <div class="space-y-2">
                <a href="{{ url('/') }}" class="flex items-center space-x-3 px-4 py-3 rounded-lg text-neutral-700 hover:bg-primary-50 hover:text-primary-700 transition-all duration-200 font-medium">
                    <i class="fas fa-home text-sm w-5"></i>
                    <span>{{ __('app.dashboard') }}</span>
                </a>
                <a href="{{ route('contracts.index') }}" class="flex items-center space-x-3 px-4 py-3 rounded-lg text-neutral-700 hover:bg-primary-50 hover:text-primary-700 transition-all duration-200 font-medium">
                    <i class="fas fa-file-contract text-sm w-5"></i>
                    <span>{{ __('app.contracts') }}</span>
                </a>
                <a href="{{ route('organizations.index') }}" class="flex items-center space-x-3 px-4 py-3 rounded-lg text-neutral-700 hover:bg-primary-50 hover:text-primary-700 transition-all duration-200 font-medium">
                    <i class="fas fa-building-columns text-sm w-5"></i>
                    <span>{{ __('app.organizations') }}</span>
                </a>
            </div>
            <div class="border-t border-neutral-200 pt-4 mt-4">
                <p class="text-neutral-500 text-sm mb-3 px-4 font-medium">{{ __('app.switch_language') }}:</p>
                <div class="space-y-1">
                    @foreach($languages as $locale => $language)
                        <a href="{{ route('language.switch', $locale) }}"
                           class="flex items-center space-x-3 px-4 py-2 rounded-lg text-neutral-600 hover:bg-primary-50 hover:text-primary-700 transition-all duration-200 @if(app()->getLocale() === $locale) bg-primary-50 text-primary-700 font-semibold @endif">
                            <i class="fas fa-globe text-sm w-5"></i>
                            <span>{{ $language['native'] }}</span>
                        </a>
                    @endforeach
                </div>
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
