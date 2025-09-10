<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Government Procurement Dashboard')</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        primary: {
                            50: '#f0f9ff',
                            100: '#e0f2fe',
                            200: '#bae6fd',
                            300: '#7dd3fc',
                            400: '#38bdf8',
                            500: '#0ea5e9',
                            600: '#0284c7',
                            700: '#0369a1',
                            800: '#075985',
                            900: '#0c4a6e',
                            950: '#082f49',
                        },
                        secondary: {
                            50: '#fdf4ff',
                            100: '#fae8ff',
                            200: '#f5d0fe',
                            300: '#f0abfc',
                            400: '#e879f9',
                            500: '#d946ef',
                            600: '#c026d3',
                            700: '#a21caf',
                            800: '#86198f',
                            900: '#701a75',
                            950: '#4a044e',
                        },
                        accent: {
                            50: '#f0fdf4',
                            100: '#dcfce7',
                            200: '#bbf7d0',
                            300: '#86efac',
                            400: '#4ade80',
                            500: '#22c55e',
                            600: '#16a34a',
                            700: '#15803d',
                            800: '#166534',
                            900: '#14532d',
                        },
                        neutral: {
                            50: '#f8fafc',
                            100: '#f1f5f9',
                            200: '#e2e8f0',
                            300: '#cbd5e1',
                            400: '#94a3b8',
                            500: '#64748b',
                            600: '#475569',
                            700: '#334155',
                            800: '#1e293b',
                            900: '#0f172a',
                        }
                    },
                    fontFamily: {
                        'sans': ['Inter', 'system-ui', 'sans-serif'],
                        'heading': ['Poppins', 'Inter', 'system-ui', 'sans-serif'],
                    },
                    animation: {
                        'fade-in': 'fadeIn 0.5s ease-in-out',
                        'slide-up': 'slideUp 0.5s ease-out',
                        'bounce-subtle': 'bounceSubtle 1s ease-in-out',
                    },
                    keyframes: {
                        fadeIn: {
                            '0%': { opacity: '0' },
                            '100%': { opacity: '1' },
                        },
                        slideUp: {
                            '0%': { transform: 'translateY(20px)', opacity: '0' },
                            '100%': { transform: 'translateY(0)', opacity: '1' },
                        },
                        bounceSubtle: {
                            '0%, 100%': { transform: 'translateY(0)' },
                            '50%': { transform: 'translateY(-5px)' },
                        }
                    },
                    boxShadow: {
                        'soft': '0 2px 15px -3px rgba(0, 0, 0, 0.07), 0 10px 20px -2px rgba(0, 0, 0, 0.04)',
                        'medium': '0 4px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 20px -5px rgba(0, 0, 0, 0.04)',
                        'strong': '0 10px 40px -10px rgba(0, 0, 0, 0.2), 0 20px 25px -5px rgba(0, 0, 0, 0.1)',
                    }
                }
            }
        }
    </script>
    <style type="text/tailwindcss">
        @layer components {
            .btn-primary {
                @apply bg-gradient-to-r from-primary-600 to-primary-700 hover:from-primary-700 hover:to-primary-800 text-white font-semibold py-3 px-8 rounded-lg shadow-medium transition-all duration-300 hover:shadow-strong hover:-translate-y-1 focus:outline-none focus:ring-4 focus:ring-primary-200;
            }

            .btn-secondary {
                @apply bg-white border-2 border-primary-200 text-primary-700 font-semibold py-3 px-8 rounded-lg shadow-soft transition-all duration-300 hover:bg-primary-50 hover:border-primary-300 hover:-translate-y-0.5 focus:outline-none focus:ring-4 focus:ring-primary-200;
            }

            .card {
                @apply bg-white dark:bg-neutral-800 rounded-2xl p-8 shadow-soft border border-neutral-100 dark:border-neutral-700 transition-all duration-300 hover:shadow-medium hover:-translate-y-1;
            }

            .card-featured {
                @apply bg-white dark:bg-neutral-800 rounded-2xl p-8 shadow-medium border-2 border-primary-100 dark:border-primary-800 transition-all duration-300 hover:shadow-strong hover:-translate-y-2 hover:border-primary-200 dark:hover:border-primary-700;
            }

            .stats-card {
                @apply bg-gradient-to-br from-white to-neutral-50 dark:from-neutral-800 dark:to-neutral-700 rounded-2xl p-8 text-center shadow-soft border border-neutral-100 dark:border-neutral-700 transition-all duration-300 hover:shadow-medium hover:-translate-y-1 hover:from-primary-50 hover:to-white dark:hover:from-neutral-700 dark:hover:to-neutral-600;
            }

            .stats-card-accent {
                @apply bg-gradient-to-br from-accent-50 to-accent-100 rounded-2xl p-8 text-center shadow-soft border border-accent-200 transition-all duration-300 hover:shadow-medium hover:-translate-y-1;
            }

            .vendor-item {
                @apply flex items-center justify-between p-6 mb-4 bg-white dark:bg-neutral-800 rounded-xl shadow-soft border border-neutral-100 dark:border-neutral-700 transition-all duration-300 hover:shadow-medium hover:-translate-y-1 hover:border-primary-200 dark:hover:border-primary-600;
            }

            .vendor-item-featured {
                @apply flex items-center justify-between p-6 mb-4 bg-gradient-to-r from-primary-50 to-accent-50 rounded-xl shadow-soft border border-primary-100 transition-all duration-300 hover:shadow-medium hover:-translate-y-1 hover:from-primary-100 hover:to-accent-100;
            }

            .clickable-organization {
                @apply cursor-pointer relative transition-all duration-300 hover:scale-105;
            }

            .vendor-item-link {
                @apply text-inherit no-underline block w-full;
            }

            .vendor-item-link:hover {
                @apply text-inherit no-underline;
            }

            .section-title {
                @apply text-3xl font-bold font-heading text-neutral-900 dark:text-neutral-100 mb-6;
            }

            .section-subtitle {
                @apply text-lg text-neutral-600 dark:text-neutral-300 mb-8 max-w-2xl;
            }

            .page-header {
                @apply text-center mb-16;
            }

            .gradient-text {
                @apply bg-gradient-to-r from-primary-600 to-secondary-600 bg-clip-text text-transparent;
            }
        }
    </style>

    @stack('styles')
</head>
<body class="min-h-screen bg-gradient-to-br from-neutral-50 via-white to-primary-50 dark:from-neutral-900 dark:via-neutral-800 dark:to-neutral-900 font-sans">
    <!-- Header -->
    <x-header />

    <!-- Main Content -->
    <main class="container mx-auto p-4 sm:px-6 lg:px-8 sm:py-12 dark:text-neutral-100">
        <div class="animate-fade-in">
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
