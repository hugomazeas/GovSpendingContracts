<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Government Procurement Dashboard')</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: {
                            50: '#f0f4ff',
                            100: '#e0e9ff',
                            500: '#667eea',
                            600: '#5a6fd8',
                            700: '#4c63d2',
                            800: '#3e56cc',
                            900: '#2e47c0',
                        },
                        secondary: {
                            500: '#764ba2',
                            600: '#6a42a0',
                        }
                    },
                    fontFamily: {
                        'sans': ['Inter', 'system-ui', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    <style type="text/tailwindcss">
        @layer components {
            .btn-primary {
                @apply bg-gradient-to-br from-primary-500 to-secondary-500 text-white font-semibold py-3 px-6 rounded-full transition-all duration-300 hover:shadow-lg hover:-translate-y-0.5;
            }
            
            .card {
                @apply bg-white/95 backdrop-blur-sm rounded-2xl p-6 shadow-xl transition-transform duration-300 hover:-translate-y-1;
            }
            
            .stats-card {
                @apply bg-white rounded-2xl p-6 text-center shadow-xl transition-transform duration-300 hover:-translate-y-1;
            }
            
            .vendor-item {
                @apply flex items-center justify-between p-4 mb-3 bg-gray-50 rounded-xl transition-all duration-300 hover:bg-gradient-to-br hover:from-blue-50 hover:to-purple-50 hover:translate-x-2 hover:-translate-y-0.5 hover:shadow-lg;
            }
            
            .clickable-organization {
                @apply cursor-pointer relative;
            }
            
            .vendor-item-link {
                @apply text-inherit no-underline block;
            }
            
            .vendor-item-link:hover {
                @apply text-inherit no-underline;
            }
        }
    </style>
    
    @stack('styles')
</head>
<body class="min-h-screen bg-gradient-to-br from-primary-500 to-secondary-500 font-sans">
    <!-- Header -->
    <x-header />
    
    <!-- Main Content -->
    <main class="container mx-auto px-4 py-8">
        <div class="bg-white/95 backdrop-blur-sm rounded-3xl p-8 shadow-2xl">
            @yield('content')
        </div>
    </main>
    
    <!-- Footer -->
    <x-footer />
    
    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.js"></script>
    
    <!-- Global Year State Management -->
    <script>
        // Global Year State Manager
        window.YearState = (function() {
            const STORAGE_KEY = 'procurement_selected_year';
            let currentYear = localStorage.getItem(STORAGE_KEY) || new Date().getFullYear().toString();
            let listeners = [];
            let availableYears = @json($availableYears ?? []);
            
            return {
                // Get current selected year
                get() {
                    return currentYear;
                },
                
                // Set current year and notify all listeners
                set(year) {
                    if (year && year !== currentYear) {
                        currentYear = year.toString();
                        localStorage.setItem(STORAGE_KEY, currentYear);
                        this.notifyListeners(currentYear);
                    }
                },
                
                // Add listener for year changes
                addListener(callback) {
                    listeners.push(callback);
                },
                
                // Remove listener
                removeListener(callback) {
                    const index = listeners.indexOf(callback);
                    if (index > -1) {
                        listeners.splice(index, 1);
                    }
                },
                
                // Notify all listeners of year change
                notifyListeners(year) {
                    listeners.forEach(callback => {
                        try {
                            callback(year);
                        } catch (error) {
                            console.error('Error in year change listener:', error);
                        }
                    });
                },
                
                // Set available years
                setAvailableYears(years) {
                    availableYears = years;
                },
                
                // Get available years
                getAvailableYears() {
                    return availableYears;
                },
                
                // Initialize year selectors on page
                initializeSelectors() {
                    document.querySelectorAll('.year-selector').forEach(select => {
                        // Set current value
                        select.value = currentYear;
                        
                        // Add change listener
                        select.addEventListener('change', function() {
                            YearState.set(this.value);
                        });
                    });
                },
                
                // Update all year selectors on page
                updateSelectors(year) {
                    document.querySelectorAll('.year-selector').forEach(select => {
                        if (select.value !== year) {
                            select.value = year;
                        }
                    });
                }
            };
        })();
        
        // Initialize when DOM is ready
        document.addEventListener('DOMContentLoaded', function() {
            YearState.initializeSelectors();
            
            // Update selectors when year changes
            YearState.addListener(function(year) {
                YearState.updateSelectors(year);
            });
        });
    </script>
    
    @stack('scripts')
</body>
</html>