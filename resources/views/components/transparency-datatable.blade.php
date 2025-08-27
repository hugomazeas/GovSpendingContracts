@props(['ajaxUrl' => '/contracts/data'])

<div class="card mt-8">
    <div class="flex items-center mb-6 pb-4 border-b border-gray-200">
        <i class="fas fa-table text-2xl text-indigo-500 mr-3"></i>
        <h3 class="text-xl font-semibold text-gray-800">Public Spending Transparency</h3>
        <div class="ml-auto text-sm text-gray-600">
            <i class="fas fa-info-circle mr-1"></i>
            How your tax dollars are being spent
        </div>
    </div>

    <div class="overflow-x-auto">
        <table id="transparency-table" class="min-w-full">
            <thead>
                <tr class="bg-gradient-to-r from-primary-500 to-secondary-500 text-white">
                    <th class="px-4 py-3 text-left text-sm font-semibold uppercase tracking-wide">
                        Reference
                    </th>
                    <th class="px-4 py-3 text-left text-sm font-semibold uppercase tracking-wide">
                        Vendor
                    </th>
                    <th class="px-4 py-3 text-left text-sm font-semibold uppercase tracking-wide">
                        When
                    </th>
                    <th class="px-4 py-3 text-left text-sm font-semibold uppercase tracking-wide">
                        Price
                    </th>
                    <th class="px-4 py-3 text-left text-sm font-semibold uppercase tracking-wide">
                        Department
                    </th>
                    <th class="px-4 py-3 text-left text-sm font-semibold uppercase tracking-wide max-w-xs">
                        Description
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
                <p class="font-medium mb-1">Understanding Government Spending</p>
                <p>This table shows real government contracts funded by taxpayers. Use the search to find specific companies, departments, or services. Click on any organization above to see their detailed spending breakdown.</p>
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
            processing: '<div class="flex items-center justify-center py-4"><i class="fas fa-spinner fa-spin mr-2"></i>Loading transparency data...</div>',
            search: "Search spending records:",
            lengthMenu: "Show _MENU_ records per page",
            info: "Showing _START_ to _END_ of _TOTAL_ spending records",
            infoEmpty: "No spending records available",
            infoFiltered: "(filtered from _MAX_ total records)",
            zeroRecords: '<div class="text-center py-8"><i class="fas fa-search text-gray-400 text-2xl mb-2"></i><p class="text-gray-600">No matching spending records found</p></div>',
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
            $('.dataTables_filter input').addClass('px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500');
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
</style>
@endpush
