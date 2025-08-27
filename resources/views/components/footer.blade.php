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
                    This dashboard provides transparent access to Canadian government contracts .
                </p>
            </div>

            <!-- Links Section -->
            <div class="text-white">
                <h3 class="text-lg font-semibold mb-4 flex items-center">
                    <i class="fas fa-external-link-alt mr-2"></i>
                    External Resources
                </h3>
                <ul class="text-white/80 text-sm space-y-2">
                    <li>
                        <a href="https://search.open.canada.ca/contracts/?page=1" class="hover:text-white transition-colors duration-200 flex items-center">
                            <i class="fas fa-globe mr-2"></i>
                            Open Government Portal (Data source)
                        </a>
                    </li>
                </ul>
            </div>
        </div>

        <div class="border-t border-white/20 mt-8 pt-6 text-center">
            <p class="text-white/60 text-sm">
                &copy; {{ date('Y') }} Government Contracts Dashboard.
                Built for transparency and public accountability.
            </p>
        </div>
    </div>
</footer>
