<footer class="bg-white/10 backdrop-blur-md border-t border-white/20 mt-12">
    <div class="container mx-auto px-4 py-8">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <!-- About Section -->
            <div class="text-white">
                <h3 class="text-lg font-semibold mb-4 flex items-center">
                    <i class="fas fa-info-circle mr-2"></i>
                    About This Dashboard
                </h3>
                <p class="text-white/80 text-sm leading-relaxed">
                    This dashboard provides transparent access to Canadian government procurement data, 
                    showing how taxpayer money is spent on contracts and services.
                </p>
            </div>
            
            <!-- Data Info Section -->
            <div class="text-white">
                <h3 class="text-lg font-semibold mb-4 flex items-center">
                    <i class="fas fa-database mr-2"></i>
                    Data Information
                </h3>
                <ul class="text-white/80 text-sm space-y-2">
                    <li class="flex items-center">
                        <i class="fas fa-calendar mr-2"></i>
                        Updated: {{ now()->format('M j, Y') }}
                    </li>
                    <li class="flex items-center">
                        <i class="fas fa-file-contract mr-2"></i>
                        {{ number_format(76378) }} Total Contracts
                    </li>
                    <li class="flex items-center">
                        <i class="fas fa-dollar-sign mr-2"></i>
                        $68.3B Total Value
                    </li>
                </ul>
            </div>
            
            <!-- Links Section -->
            <div class="text-white">
                <h3 class="text-lg font-semibold mb-4 flex items-center">
                    <i class="fas fa-external-link-alt mr-2"></i>
                    External Resources
                </h3>
                <ul class="text-white/80 text-sm space-y-2">
                    <li>
                        <a href="https://open.canada.ca" class="hover:text-white transition-colors duration-200 flex items-center">
                            <i class="fas fa-globe mr-2"></i>
                            Open Government Portal
                        </a>
                    </li>
                    <li>
                        <a href="https://www.tpsgc-pwgsc.gc.ca" class="hover:text-white transition-colors duration-200 flex items-center">
                            <i class="fas fa-building mr-2"></i>
                            Public Services Canada
                        </a>
                    </li>
                    <li>
                        <a href="https://github.com" class="hover:text-white transition-colors duration-200 flex items-center">
                            <i class="fab fa-github mr-2"></i>
                            Source Code
                        </a>
                    </li>
                </ul>
            </div>
        </div>
        
        <div class="border-t border-white/20 mt-8 pt-6 text-center">
            <p class="text-white/60 text-sm">
                &copy; {{ date('Y') }} Government Procurement Dashboard. 
                Built for transparency and public accountability.
            </p>
        </div>
    </div>
</footer>