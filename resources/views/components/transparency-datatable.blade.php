@props(['ajaxUrl' => '/contracts/data'])

<div class="card mt-8">
    <div class="flex items-center mb-6 pb-4 border-b border-gray-200">
        <i class="fas fa-table text-2xl text-indigo-500 mr-3"></i>
        <h3 class="text-xl font-semibold text-gray-800">{{ __('app.public_spending_transparency') }}</h3>
    </div>

    <div class="overflow-x-auto">
        <table id="transparency-table" class="min-w-full">
            <thead>
                <tr class="bg-gradient-to-r from-primary-500 to-secondary-500 text-white">
                    <th class="px-4 py-3 text-left text-sm font-semibold uppercase tracking-wide">
                        {{ __('app.reference') }}
                    </th>
                    <th class="px-4 py-3 text-left text-sm font-semibold uppercase tracking-wide">
                        {{ __('app.vendor') }}
                    </th>
                    <th class="px-4 py-3 text-left text-sm font-semibold uppercase tracking-wide">
                        {{ __('app.when') }}
                    </th>
                    <th class="px-4 py-3 text-left text-sm font-semibold uppercase tracking-wide">
                        {{ __('app.price') }}
                    </th>
                    <th class="px-4 py-3 text-left text-sm font-semibold uppercase tracking-wide">
                        {{ __('app.department') }}
                    </th>
                    <th class="px-4 py-3 text-left text-sm font-semibold uppercase tracking-wide max-w-xs">
                        {{ __('app.description') }}
                    </th>
                </tr>
            </thead>
            <tbody>
                <!-- DataTable will populate this -->
            </tbody>
        </table>
    </div>

    <div class="mt-4 p-4 bg-blue-50 rounded-lg">
        <div class="flex items-start space-x-3">
            <i class="fas fa-lightbulb text-blue-500 mt-0.5"></i>
            <div class="text-sm text-blue-700">
                <p class="font-medium mb-1">{{ __('app.understanding_government_spending') }}</p>
                <p>{{ __('app.government_spending_description') }}</p>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script>
$(document).ready(function() {
    window.dataTable = $('#transparency-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ $ajaxUrl }}',
            type: 'GET'
        },
        columns: [
            { data: 'reference_number', name: 'reference_number', className: 'text-sm font-mono' },
            { data: 'vendor_name', name: 'vendor_name', className: 'font-medium', render: function(data, type, row) {
                return type === 'display' || type === 'type' ? data : $(data).text();
            }},
            { data: 'contract_date', name: 'contract_date', className: 'text-sm' },
            { data: 'total_contract_value', name: 'total_contract_value', className: 'text-right font-semibold text-green-600' },
            { data: 'organization', name: 'organization', className: 'text-sm', render: function(data, type, row) {
                return type === 'display' || type === 'type' ? data : $(data).text();
            }},
            { data: 'description_of_work_english', name: 'description_of_work_english', className: 'text-sm max-w-xs truncate' }
        ],
        order: [[2, 'desc']], // Order by date, newest first
        pageLength: 25,
        lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
        language: {
            processing: '<div class="flex items-center justify-center py-4"><i class="fas fa-spinner fa-spin mr-2"></i>{{ __('app.loading_transparency_data') }}</div>',
            search: "{{ __('app.search_spending_records') }}",
            lengthMenu: "{{ __('app.show_records_per_page') }}",
            info: "{{ __('app.showing_records') }}",
            infoEmpty: "{{ __('app.no_records_available') }}",
            infoFiltered: "{{ __('app.filtered_from_total') }}",
            zeroRecords: '<div class="text-center py-8"><i class="fas fa-search text-gray-400 text-2xl mb-2"></i><p class="text-gray-600">{{ __('app.no_matching_records') }}</p></div>',
            paginate: {
                first: "{{ __('app.first') }}",
                last: "{{ __('app.last') }}",
                next: "{{ __('app.next') }}",
                previous: "{{ __('app.previous') }}"
            }
        },
        dom: '<"flex flex-col md:flex-row md:items-center md:justify-between mb-4"<"mb-2 md:mb-0"l><"relative"f>>rtip',
        drawCallback: function(settings) {
            // Add custom styling after each draw
            $('.dataTables_info, .dataTables_paginate').addClass('mt-4');
            $('.dataTables_filter input').addClass('px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500');
            $('.dataTables_length select').addClass('px-3 py-1 border border-gray-300 rounded-lg');
            
            // Make table rows clickable
            $('#transparency-table tbody tr').each(function() {
                const $row = $(this);
                const data = window.dataTable.row($row).data();
                if (data && data.id) {
                    $row.addClass('cursor-pointer hover:bg-primary-50 transition-colors');
                    $row.off('click.contract-detail').on('click.contract-detail', function(e) {
                        // Don't trigger if clicking on a link
                        if ($(e.target).closest('a').length === 0) {
                            window.location.href = '/contract/' + data.id;
                        }
                    });
                }
            });
        }
    });
});
</script>

<link href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css" rel="stylesheet">
<style>
    /* Custom DataTable styling for better integration with Tailwind */
    .dataTables_wrapper .dataTables_filter input,
    .dataTables_wrapper .dataTables_length select {
        @apply border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-primary-500 focus:border-primary-500;
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button {
        @apply px-3 py-1 mx-1 text-sm border border-gray-300 rounded bg-white hover:bg-gray-50;
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button.current {
        @apply bg-primary-500 text-white border-primary-500;
    }

    table.dataTable tbody tr:hover {
        @apply bg-blue-50;
    }
    
    table.dataTable tbody tr.cursor-pointer:hover {
        @apply bg-primary-50 shadow-sm;
    }
</style>
@endpush
