@extends('layouts.app')

@section('title', 'Organizations - Government Procurement Dashboard')

@section('content')
    <!-- Page Header -->
    <div class="text-center mb-8">
        <h1 class="text-4xl font-bold text-gray-800 mb-4">
            <i class="fas fa-building-columns mr-3 text-purple-600"></i>
            Organizations
        </h1>
        <p class="text-xl text-gray-600 max-w-3xl mx-auto">
            Government procurement spending by organization. We use the term "organization" because it includes 
            non-ministerial entities, departments, agencies, and other government bodies involved in procurement processes.
        </p>
    </div>

    <!-- Organizations Table -->
    <div class="card">
        <div class="flex items-center mb-6 pb-4 border-b border-gray-200">
            <i class="fas fa-table text-2xl text-purple-600 mr-3"></i>
            <h3 class="text-xl font-semibold text-gray-800">Organizations Spending Overview</h3>
            <div class="ml-auto text-sm text-gray-600">
                <i class="fas fa-info-circle mr-1"></i>
                Last 3 years spending comparison
            </div>
        </div>
        
        <div class="overflow-x-auto">
            <table id="organizations-table" class="min-w-full">
                <thead>
                    <tr class="bg-gradient-to-r from-purple-500 to-indigo-500 text-white">
                        <th class="px-4 py-3 text-left text-sm font-semibold uppercase tracking-wide">
                            Organization
                        </th>
                        <th class="px-4 py-3 text-right text-sm font-semibold uppercase tracking-wide">
                            {{ date('Y') - 1 }}
                        </th>
                        <th class="px-4 py-3 text-right text-sm font-semibold uppercase tracking-wide">
                            {{ date('Y') - 2 }}
                        </th>
                        <th class="px-4 py-3 text-right text-sm font-semibold uppercase tracking-wide">
                            {{ date('Y') - 3 }}
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <!-- DataTable will populate this -->
                </tbody>
            </table>
        </div>

        <div class="mt-4 p-4 bg-purple-50 rounded-lg">
            <div class="flex items-start space-x-3">
                <i class="fas fa-lightbulb text-purple-500 mt-0.5"></i>
                <div class="text-sm text-purple-700">
                    <p class="font-medium mb-1">Understanding Organization Spending</p>
                    <p>This table shows spending by government organizations over the last 3 years. The change indicator shows year-over-year percentage growth. Click on any organization to see detailed spending breakdown and vendor relationships.</p>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script>
$(document).ready(function() {
    const currentYear = {{ date('Y') }};
    
    $('#organizations-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route('organizations.data') }}',
            type: 'GET'
        },
        columns: [
            { 
                data: 'organization', 
                name: 'organization',
                className: 'font-medium',
                render: function(data, type, row) {
                    return type === 'display' || type === 'type' ? data : $(data).text();
                }
            },
            { 
                data: 'spending_' + (currentYear - 1), 
                name: 'spending_year_1',
                className: 'text-right font-semibold text-green-600',
                render: function(data, type, row) {
                    if (type === 'display' || type === 'type') {
                        const changeIndicator = row.change_year_1 || '';
                        return data + (changeIndicator ? '<br><small class="text-xs text-gray-500">' + changeIndicator + '</small>' : '');
                    }
                    return data;
                }
            },
            { 
                data: 'spending_' + (currentYear - 2), 
                name: 'spending_year_2',
                className: 'text-right font-semibold text-green-600',
                render: function(data, type, row) {
                    if (type === 'display' || type === 'type') {
                        const changeIndicator = row.change_year_2 || '';
                        return data + (changeIndicator ? '<br><small class="text-xs text-gray-500">' + changeIndicator + '</small>' : '');
                    }
                    return data;
                }
            },
            { 
                data: 'spending_' + (currentYear - 3), 
                name: 'spending_year_3',
                className: 'text-right font-semibold text-green-600',
                render: function(data, type, row) {
                    if (type === 'display' || type === 'type') {
                        const changeIndicator = row.change_year_3 || '';
                        return data + (changeIndicator ? '<br><small class="text-xs text-gray-500">' + changeIndicator + '</small>' : '');
                    }
                    return data;
                }
            }
        ],
        order: [[1, 'desc']], // Order by most recent year spending by default
        pageLength: 25,
        lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
        language: {
            processing: '<div class="flex items-center justify-center py-4"><div class="animate-spin rounded-full h-6 w-6 border-b-2 border-purple-600 mr-3"></div><span class="text-purple-600 font-medium">Loading organizations...</span></div>',
            search: "Search organizations:",
            lengthMenu: "Show _MENU_ organizations per page",
            info: "Showing _START_ to _END_ of _TOTAL_ organizations",
            infoEmpty: "No organizations available",
            infoFiltered: "(filtered from _MAX_ total organizations)",
            zeroRecords: '<div class="text-center py-8"><i class="fas fa-search text-gray-400 text-2xl mb-2"></i><p class="text-gray-600">No matching organizations found</p></div>',
            paginate: {
                first: "First",
                last: "Last",
                next: "Next",
                previous: "Previous"
            }
        },
        dom: '<"flex flex-col md:flex-row md:items-center md:justify-between mb-4"<"mb-2 md:mb-0"l><"relative"f>>rtip',
        drawCallback: function(settings) {
            // Add custom styling after each draw
            $('.dataTables_info, .dataTables_paginate').addClass('mt-4');
            $('.dataTables_filter input').addClass('px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500');
            $('.dataTables_length select').addClass('px-3 py-1 border border-gray-300 rounded-lg');
        }
    });
});
</script>

<link href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css" rel="stylesheet">
<style>
    /* Custom DataTable styling for better integration with Tailwind */
    .dataTables_wrapper .dataTables_filter input,
    .dataTables_wrapper .dataTables_length select {
        @apply border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-purple-500 focus:border-purple-500;
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button {
        @apply px-3 py-1 mx-1 text-sm border border-gray-300 rounded bg-white hover:bg-gray-50;
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button.current {
        @apply bg-purple-500 text-white border-purple-500;
    }

    table.dataTable tbody tr:hover {
        @apply bg-purple-50;
    }
</style>
@endpush