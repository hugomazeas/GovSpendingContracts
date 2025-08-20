<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Government Procurement Dashboard</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- DataTables CSS -->
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
        
        .main-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            margin: 20px;
            padding: 30px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
        }
        
        .header-section {
            text-align: center;
            margin-bottom: 40px;
            padding: 20px 0;
        }
        
        .header-section h1 {
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 10px;
            font-size: 2.5rem;
        }
        
        .header-section p {
            color: #7f8c8d;
            font-size: 1.1rem;
        }
        
        .stats-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            text-align: center;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
            border: none;
        }
        
        .stats-card:hover {
            transform: translateY(-5px);
        }
        
        .stats-icon {
            font-size: 3rem;
            margin-bottom: 15px;
        }
        
        .stats-number {
            font-size: 2rem;
            font-weight: 700;
            margin: 10px 0;
        }
        
        .stats-label {
            color: #7f8c8d;
            font-weight: 500;
            text-transform: uppercase;
            font-size: 0.9rem;
            letter-spacing: 1px;
        }
        
        .leaderboard-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
        }
        
        .leaderboard-header {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #ecf0f1;
        }
        
        .leaderboard-header i {
            font-size: 1.5rem;
            margin-right: 10px;
            color: #f39c12;
        }
        
        .leaderboard-header h3 {
            margin: 0;
            font-weight: 600;
            color: #2c3e50;
        }
        
        .vendor-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 15px 20px;
            margin-bottom: 10px;
            background: #f8f9fa;
            border-radius: 10px;
            transition: all 0.3s ease;
        }
        
        .vendor-item:hover {
            background: #e3f2fd;
            transform: translateX(5px);
        }
        
        .vendor-rank {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            width: 35px;
            height: 35px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            margin-right: 15px;
        }
        
        .vendor-info {
            flex-grow: 1;
        }
        
        .vendor-name {
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 3px;
        }
        
        .vendor-details {
            font-size: 0.9rem;
            color: #7f8c8d;
        }
        
        .vendor-metric {
            text-align: right;
            font-weight: 600;
        }
        
        .metric-primary {
            color: #27ae60;
            font-size: 1.1rem;
        }
        
        .metric-secondary {
            color: #7f8c8d;
            font-size: 0.9rem;
        }
        
        .contracts-section {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            margin-top: 30px;
        }
        
        .section-title {
            display: flex;
            align-items: center;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 2px solid #ecf0f1;
        }
        
        .section-title i {
            font-size: 1.5rem;
            margin-right: 10px;
            color: #3498db;
        }
        
        .section-title h3 {
            margin: 0;
            font-weight: 600;
            color: #2c3e50;
        }
        
        .table th {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            font-weight: 600;
            border: none;
            text-transform: uppercase;
            font-size: 0.85rem;
            letter-spacing: 1px;
        }
        
        .table td {
            vertical-align: middle;
            border-color: #ecf0f1;
        }
        
        .table-hover tbody tr:hover {
            background-color: rgba(102, 126, 234, 0.1);
        }
        
        .description-column {
            max-width: 300px;
            word-wrap: break-word;
        }
        
        .dataTables_wrapper .dataTables_filter input {
            border-radius: 25px;
            border: 2px solid #ecf0f1;
            padding: 8px 15px;
            margin-left: 10px;
            transition: all 0.3s ease;
        }
        
        .dataTables_wrapper .dataTables_filter input:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        
        .dataTables_wrapper .dataTables_length select {
            border-radius: 10px;
            border: 2px solid #ecf0f1;
        }
        
        .btn-gradient {
            background: linear-gradient(135deg, #667eea, #764ba2);
            border: none;
            border-radius: 25px;
            color: white;
            font-weight: 600;
        }
        
        .btn-gradient:hover {
            background: linear-gradient(135deg, #5a6fd8, #6a42a0);
            color: white;
        }
        
        .vendor-item-link {
            text-decoration: none;
            color: inherit;
            display: block;
        }
        
        .vendor-item-link:hover {
            text-decoration: none;
            color: inherit;
        }
        
        .clickable-organization {
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
        }
        
        .clickable-organization:hover {
            background: linear-gradient(135deg, #e3f2fd, #f3e5f5) !important;
            transform: translateX(8px) translateY(-2px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.15);
        }
        
        .clickable-organization:hover .vendor-name {
            color: #667eea;
        }
        
        .clickable-organization::after {
            content: "→";
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            opacity: 0;
            transition: opacity 0.3s ease;
            color: #667eea;
            font-weight: bold;
            font-size: 1.2rem;
        }
        
        .clickable-organization:hover::after {
            opacity: 1;
        }
        
        @media (max-width: 768px) {
            .main-container {
                margin: 10px;
                padding: 20px;
            }
            
            .header-section h1 {
                font-size: 2rem;
            }
            
            .stats-card {
                margin-bottom: 20px;
            }
            
            .vendor-item {
                flex-direction: column;
                text-align: center;
            }
            
            .vendor-rank {
                margin-bottom: 10px;
            }
            
            .vendor-metric {
                text-align: center;
                margin-top: 10px;
            }
        }
    </style>
</head>
<body>
    <div class="main-container">
        <!-- Header Section -->
        <div class="header-section">
            <h1><i class="fas fa-chart-line"></i> Government Procurement Dashboard</h1>
            <p>Comprehensive overview of government procurement contracts and vendor performance</p>
        </div>
        
        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-md-3 col-sm-6 mb-3">
                <div class="stats-card">
                    <div class="stats-icon text-primary">
                        <i class="fas fa-file-contract"></i>
                    </div>
                    <div class="stats-number text-primary">{{ number_format($stats['total_contracts']) }}</div>
                    <div class="stats-label">Total Contracts</div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 mb-3">
                <div class="stats-card">
                    <div class="stats-icon text-success">
                        <i class="fas fa-dollar-sign"></i>
                    </div>
                    <div class="stats-number text-success">${{ number_format($stats['total_value'], 0) }}</div>
                    <div class="stats-label">Total Value</div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 mb-3">
                <div class="stats-card">
                    <div class="stats-icon text-info">
                        <i class="fas fa-building"></i>
                    </div>
                    <div class="stats-number text-info">{{ number_format($stats['unique_vendors']) }}</div>
                    <div class="stats-label">Unique Vendors</div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 mb-3">
                <div class="stats-card">
                    <div class="stats-icon text-warning">
                        <i class="fas fa-calculator"></i>
                    </div>
                    <div class="stats-number text-warning">${{ number_format($stats['avg_contract_value'], 0) }}</div>
                    <div class="stats-label">Avg Contract Value</div>
                </div>
            </div>
        </div>
        
        <!-- Vendor Leaderboards - First Row -->
        <div class="row mb-4">
            <div class="col-lg-6 mb-4">
                <div class="leaderboard-card">
                    <div class="leaderboard-header">
                        <i class="fas fa-trophy"></i>
                        <h3>Top Vendors by Contract Count</h3>
                    </div>
                    @foreach($topVendorsByCount->take(5) as $index => $vendor)
                    <div class="vendor-item">
                        <div class="vendor-rank">{{ $index + 1 }}</div>
                        <div class="vendor-info">
                            <div class="vendor-name">{{ Str::title(strtolower($vendor->vendor_name)) }}</div>
                            <div class="vendor-details">Total Value: ${{ number_format($vendor->total_value, 0) }}</div>
                        </div>
                        <div class="vendor-metric">
                            <div class="metric-primary">{{ number_format($vendor->contract_count) }}</div>
                            <div class="metric-secondary">contracts</div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            
            <div class="col-lg-6 mb-4">
                <div class="leaderboard-card">
                    <div class="leaderboard-header">
                        <i class="fas fa-dollar-sign"></i>
                        <h3>Top Vendors by Total Value</h3>
                    </div>
                    @foreach($topVendorsByValue->take(5) as $index => $vendor)
                    <div class="vendor-item">
                        <div class="vendor-rank">{{ $index + 1 }}</div>
                        <div class="vendor-info">
                            <div class="vendor-name">{{ Str::title(strtolower($vendor->vendor_name)) }}</div>
                            <div class="vendor-details">{{ number_format($vendor->contract_count) }} contracts</div>
                        </div>
                        <div class="vendor-metric">
                            <div class="metric-primary">${{ number_format($vendor->total_value, 0) }}</div>
                            <div class="metric-secondary">total value</div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        
        <!-- Organization Leaderboard - Second Row -->
        <div class="row mb-4">
            <div class="col-lg-8 offset-lg-2 mb-4">
                <div class="leaderboard-card">
                    <div class="leaderboard-header">
                        <i class="fas fa-building-columns"></i>
                        <h3>Top Government Organizations by Spending</h3>
                    </div>
                    @foreach($topOrganizationsBySpending->take(5) as $index => $org)
                    <a href="{{ route('organization.detail', ['organization' => urlencode($org->organization)]) }}" class="vendor-item-link">
                        <div class="vendor-item clickable-organization">
                            <div class="vendor-rank">{{ $index + 1 }}</div>
                            <div class="vendor-info">
                                <div class="vendor-name">{{ $org->organization }}</div>
                                <div class="vendor-details">{{ number_format($org->contract_count) }} contracts</div>
                            </div>
                            <div class="vendor-metric">
                                @if($org->total_spending >= 1000000000)
                                    <div class="metric-primary">${{ number_format($org->total_spending / 1000000000, 1) }}B</div>
                                    <div class="metric-secondary">total spent</div>
                                @else
                                    <div class="metric-primary">${{ number_format($org->total_spending / 1000000, 1) }}M</div>
                                    <div class="metric-secondary">total spent</div>
                                @endif
                            </div>
                        </div>
                    </a>
                    @endforeach
                </div>
            </div>
        </div>
        
        <!-- Contracts Table Section -->
        <div class="contracts-section">
            <div class="section-title">
                <i class="fas fa-table"></i>
                <h3>All Procurement Contracts</h3>
            </div>
            
            <div class="table-responsive">
                <table id="contracts-table" class="table table-striped table-hover" style="width:100%">
                    <thead>
                        <tr>
                            <th>Reference Number</th>
                            <th>Vendor Name</th>
                            <th>Contract Date</th>
                            <th>Contract Value</th>
                            <th>Organization</th>
                            <th class="description-column">Description</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

    <script>
        $(document).ready(function() {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            $('#contracts-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{{ route("contracts.data") }}',
                    type: 'GET'
                },
                columns: [
                    { data: 'reference_number', name: 'reference_number' },
                    { data: 'vendor_name', name: 'vendor_name' },
                    { data: 'contract_date', name: 'contract_date' },
                    { 
                        data: 'total_contract_value', 
                        name: 'total_contract_value',
                        className: 'text-end'
                    },
                    { data: 'organization', name: 'organization' },
                    { 
                        data: 'description_of_work_english', 
                        name: 'description_of_work_english',
                        className: 'description-column'
                    }
                ],
                order: [[2, 'desc']],
                pageLength: 25,
                lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
                responsive: true,
                language: {
                    processing: "Loading data...",
                    search: "Search contracts:",
                    lengthMenu: "Show _MENU_ contracts per page",
                    info: "Showing _START_ to _END_ of _TOTAL_ contracts",
                    infoEmpty: "No contracts available",
                    infoFiltered: "(filtered from _MAX_ total contracts)",
                    paginate: {
                        first: "First",
                        last: "Last",
                        next: "Next",
                        previous: "Previous"
                    }
                },
                drawCallback: function(settings) {
                    // Add some styling after each draw
                    $('.dataTables_info, .dataTables_paginate').addClass('mt-3');
                }
            });
        });
    </script>
</body>
</html>