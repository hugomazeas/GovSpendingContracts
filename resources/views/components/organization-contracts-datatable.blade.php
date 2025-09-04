@props(['ajaxUrl', 'organizationName'])

<div class="card">
    <div class="flex items-center mb-6 pb-4 border-b border-gray-200">
        <i class="fas fa-table text-2xl text-indigo-500 mr-3"></i>
        <h3 class="text-xl font-semibold text-gray-800">
            {{ __('app.organization_contracts') }} - {{ $organizationName }}
        </h3>
    </div>

    <div class="overflow-x-auto">
        <table id="organization-contracts-table" class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        {{ __('app.vendor_name') }}
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        {{ __('app.reference_number') }}
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        {{ __('app.contract_date') }}
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        {{ __('app.contract_value') }}
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        {{ __('app.description') }}
                    </th>
                </tr>
            </thead>
        </table>
    </div>
</div>

<script>
$(document).ready(function() {
    // Wait a bit for YearState to be properly initialized
    setTimeout(function() {
        // Initialize DataTable
        window.organizationContractsTable = $('#organization-contracts-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ $ajaxUrl }}',
                type: 'GET',
                data: function(d) {
                    if (typeof YearState !== 'undefined') {
                        d.year = YearState.get();
                    }
                }
            },
            columns: [
                {
                    data: 'vendor_name',
                    name: 'vendor_name',
                    render: function(data, type, row) {
                        if (type === 'display' && data) {
                            return '<span class="font-medium text-gray-900">' + data + '</span>';
                        }
                        return data || '-';
                    }
                },
                {
                    data: 'reference_number',
                    name: 'reference_number',
                    render: function(data, type, row) {
                        if (type === 'display' && data) {
                            return '<span class="font-mono text-sm text-gray-800">' + data + '</span>';
                        }
                        return data || '-';
                    }
                },
                {
                    data: 'contract_date',
                    name: 'contract_date',
                    render: function(data, type, row) {
                        if (type === 'display' && data) {
                            const date = new Date(data);
                            return '<span class="text-gray-700">' + date.toLocaleDateString() + '</span>';
                        }
                        return data || '-';
                    }
                },
                {
                    data: 'total_contract_value',
                    name: 'total_contract_value',
                    render: function(data, type, row) {
                        if (type === 'display' && data && data !== '-') {
                            return '<span class="font-semibold text-green-600">' + data + '</span>';
                        }
                        return data || '-';
                    }
                },
                {
                    data: 'description_of_work_english',
                    name: 'description_of_work_english',
                    orderable: false,
                    render: function(data, type, row) {
                        if (type === 'display' && data && data !== '-') {
                            return '<span class="text-gray-600 text-sm">' + data + '</span>';
                        }
                        return data || '-';
                    }
                }
            ],
            order: [[2, 'desc']], // Order by date, newest first
            pageLength: 25,
            lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
            language: {
                processing: '<div class="flex items-center justify-center py-4"><i class="fas fa-spinner fa-spin mr-2"></i>Loading contracts...</div>',
                search: "Search contracts:",
                lengthMenu: "Show _MENU_ contracts per page",
                info: "Showing _START_ to _END_ of _TOTAL_ contracts",
                infoEmpty: "No contracts available",
                infoFiltered: "(filtered from _MAX_ total contracts)",
                zeroRecords: '<div class="text-center py-8"><i class="fas fa-search text-gray-400 text-2xl mb-2"></i><p class="text-gray-600">No matching contracts found</p></div>',
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
                
                // Make table rows clickable
                $('#organization-contracts-table tbody tr').each(function() {
                    const $row = $(this);
                    const data = window.organizationContractsTable.row($row).data();
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
            },
            columnDefs: [
                { responsivePriority: 1, targets: 0 }, // Vendor name
                { responsivePriority: 2, targets: 3 }, // Contract value
                { responsivePriority: 3, targets: 2 }, // Date
                { responsivePriority: 4, targets: 1 }, // Reference
                { responsivePriority: 5, targets: 4 }  // Description
            ]
        });

        // Listen for year state changes
        if (typeof YearState !== 'undefined') {
            YearState.addListener(function(year) {
                window.organizationContractsTable.ajax.reload();
            });
        }
    }, 100); // Wait 100ms for YearState initialization
});
</script>

@push('scripts')
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
@endpush

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