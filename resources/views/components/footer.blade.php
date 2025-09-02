<footer class="bg-white border-t border-neutral-200 mt-20">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8 py-16">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-12">
            <div>
                <h3 class="text-lg font-semibold font-heading text-neutral-900 mb-6">
                    Quick Navigation
                </h3>
                <ul class="space-y-3">
                    <li>
                        <a href="{{ url('/') }}"
                           class="text-neutral-600 hover:text-primary-600 transition-colors duration-200 flex items-center">
                            <i class="fas fa-home text-sm mr-3 w-4"></i>
                            Dashboard
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('organizations.index') }}"
                           class="text-neutral-600 hover:text-primary-600 transition-colors duration-200 flex items-center">
                            <i class="fas fa-building-columns text-sm mr-3 w-4"></i>
                            Organizations
                        </a>
                    </li>
                </ul>
            </div>

            <!-- Data & Resources -->
            <div>
                <h3 class="text-lg font-semibold font-heading text-neutral-900 mb-6">
                    Data & Resources
                </h3>
                <ul class="space-y-3">
                    <a href="https://www.linkedin.com/in/hugo-maz%C3%A9as-076914231/"
                       class="text-neutral-400 flex space-x-4 hover:text-primary-600 transition-colors duration-200">
                        <i class="fab fa-linkedin text-xl"></i>
                        <p class="text-neutral-600 leading-relaxed mb-4">
                            By Hugo Mazéas
                        </p>
                    </a>
                    <li>
                        <a href="https://search.open.canada.ca/contracts/?page=1" target="_blank"
                           rel="noopener noreferrer"
                           class="text-neutral-600 hover:text-primary-600 transition-colors duration-200 flex items-center">
                            <i class="fas fa-external-link-alt text-sm mr-3 w-4"></i>
                            Open Government Portal
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</footer>
