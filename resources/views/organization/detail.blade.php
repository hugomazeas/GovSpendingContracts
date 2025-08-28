<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $decodedOrganization }} - Spending Details</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
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
            border-bottom: 2px solid #ecf0f1;
        }

        .header-section h1 {
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 10px;
            font-size: 2.2rem;
        }

        .header-section p {
            color: #7f8c8d;
            font-size: 1.1rem;
        }

        .back-button {
            background: linear-gradient(135deg, #667eea, #764ba2);
            border: none;
            border-radius: 25px;
            color: white;
            font-weight: 600;
            padding: 10px 20px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            margin-bottom: 20px;
            transition: all 0.3s ease;
        }

        .back-button:hover {
            background: linear-gradient(135deg, #5a6fd8, #6a42a0);
            color: white;
            transform: translateY(-2px);
        }

        .back-button i {
            margin-right: 8px;
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
            font-size: 2.5rem;
            margin-bottom: 15px;
        }

        .stats-number {
            font-size: 1.8rem;
            font-weight: 700;
            margin: 10px 0;
        }

        .stats-label {
            color: #7f8c8d;
            font-weight: 500;
            text-transform: uppercase;
            font-size: 0.85rem;
            letter-spacing: 1px;
        }

        .section-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
        }

        .section-header {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #ecf0f1;
        }

        .section-header i {
            font-size: 1.5rem;
            margin-right: 10px;
            color: #3498db;
        }

        .section-header h3 {
            margin: 0;
            font-weight: 600;
            color: #2c3e50;
        }

        .vendor-item, .contract-item, .year-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 15px 20px;
            margin-bottom: 10px;
            background: #f8f9fa;
            border-radius: 10px;
            transition: all 0.3s ease;
        }

        .vendor-item:hover, .contract-item:hover, .year-item:hover {
            background: #e3f2fd;
            transform: translateX(5px);
        }

        .item-rank {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            margin-right: 15px;
            font-size: 0.9rem;
        }

        .item-info {
            flex-grow: 1;
        }

        .item-name {
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 3px;
        }

        .item-details {
            font-size: 0.9rem;
            color: #7f8c8d;
        }

        .item-metric {
            text-align: right;
            font-weight: 600;
        }

        .metric-primary {
            color: #27ae60;
            font-size: 1rem;
        }

        .metric-secondary {
            color: #7f8c8d;
            font-size: 0.85rem;
        }

        .contract-description {
            max-width: 300px;
            word-wrap: break-word;
        }

        @media (max-width: 768px) {
            .main-container {
                margin: 10px;
                padding: 20px;
            }

            .header-section h1 {
                font-size: 1.8rem;
            }

            .stats-card {
                margin-bottom: 20px;
            }

            .vendor-item, .contract-item, .year-item {
                flex-direction: column;
                text-align: center;
            }

            .item-rank {
                margin-bottom: 10px;
            }

            .item-metric {
                text-align: center;
                margin-top: 10px;
            }
        }
    </style>
</head>
<body>
    <div class="main-container">
        <!-- Back Button -->
        <a href="{{ url('/') }}" class="back-button">
            <i class="fas fa-arrow-left"></i>
            {{ __('app.back_to_dashboard') }}
        </a>

        <!-- Header Section -->
        <div class="header-section">
            <h1><i class="fas fa-building-columns"></i> {{ $decodedOrganization }}</h1>
            <p>{{ __('app.detailed_spending_analysis') }}</p>
        </div>

        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-md-3 col-sm-6 mb-3">
                <div class="stats-card">
                    <div class="stats-icon text-primary">
                        <i class="fas fa-file-contract"></i>
                    </div>
                    <div class="stats-number text-primary">{{ number_format($organizationStats['total_contracts']) }}</div>
                    <div class="stats-label">{{ __('app.total_contracts') }}</div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 mb-3">
                <div class="stats-card">
                    <div class="stats-icon text-success">
                        <i class="fas fa-dollar-sign"></i>
                    </div>
                    <div class="stats-number text-success">
                        @if($organizationStats['total_spending'] >= 1000000000)
                            ${{ number_format($organizationStats['total_spending'] / 1000000000, 1) }}B
                        @else
                            ${{ number_format($organizationStats['total_spending'] / 1000000, 1) }}M
                        @endif
                    </div>
                    <div class="stats-label">{{ __('app.total_spending') }}</div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 mb-3">
                <div class="stats-card">
                    <div class="stats-icon text-info">
                        <i class="fas fa-building"></i>
                    </div>
                    <div class="stats-number text-info">{{ number_format($organizationStats['unique_vendors']) }}</div>
                    <div class="stats-label">{{ __('app.unique_vendors') }}</div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 mb-3">
                <div class="stats-card">
                    <div class="stats-icon text-warning">
                        <i class="fas fa-calculator"></i>
                    </div>
                    <div class="stats-number text-warning">${{ number_format($organizationStats['avg_contract_value'], 0) }}</div>
                    <div class="stats-label">{{ __('app.avg_contract_value') }}</div>
                </div>
            </div>
        </div>

        <!-- Top Vendors and Yearly Spending -->
        <div class="row mb-4">
            <div class="col-lg-6 mb-4">
                <div class="section-card">
                    <div class="section-header">
                        <i class="fas fa-trophy"></i>
                        <h3>{{ __('app.top_vendors') }}</h3>
                    </div>
                    @foreach($topVendorsForOrg->take(10) as $index => $vendor)
                    <div class="vendor-item">
                        <div class="item-rank">{{ $index + 1 }}</div>
                        <div class="item-info">
                            <div class="item-name">{{ Str::title(strtolower($vendor->vendor_name)) }}</div>
                            <div class="item-details">{{ number_format($vendor->contract_count) }} {{ __('app.contracts') }}</div>
                        </div>
                        <div class="item-metric">
                            <div class="metric-primary">${{ number_format($vendor->total_value, 0) }}</div>
                            <div class="metric-secondary">{{ __('app.total_value') }}</div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            <div class="col-lg-6 mb-4">
                <div class="section-card">
                    <div class="section-header">
                        <i class="fas fa-chart-line"></i>
                        <h3>{{ __('app.spending_by_year') }}</h3>
                    </div>
                    @foreach($contractsByYear->take(8) as $index => $year)
                    <div class="year-item">
                        <div class="item-rank">{{ $year->contract_year }}</div>
                        <div class="item-info">
                            <div class="item-name">{{ number_format($year->contract_count) }} {{ __('app.contracts') }}</div>
                            <div class="item-details">{{ __('app.average_label') }} ${{ number_format($year->total_spending / $year->contract_count, 0) }}</div>
                        </div>
                        <div class="item-metric">
                            <div class="metric-primary">
                                @if($year->total_spending >= 1000000000)
                                    ${{ number_format($year->total_spending / 1000000000, 1) }}B
                                @else
                                    ${{ number_format($year->total_spending / 1000000, 1) }}M
                                @endif
                            </div>
                            <div class="metric-secondary">{{ __('app.total_spent') }}</div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Top Contracts -->
        <div class="section-card">
            <div class="section-header">
                <i class="fas fa-list-alt"></i>
                <h3>{{ __('app.largest_contract') }}</h3>
            </div>

            @foreach($topContracts->take(15) as $index => $contract)
            <div class="contract-item">
                <div class="item-rank">{{ $index + 1 }}</div>
                <div class="item-info">
                    <div class="item-name">{{ Str::title(strtolower($contract->vendor_name)) }}</div>
                    <div class="item-details contract-description">
                        {{ $contract->description_of_work_english ?
                            (strlen($contract->description_of_work_english) > 80 ?
                                substr($contract->description_of_work_english, 0, 80) . '...' :
                                $contract->description_of_work_english) :
                            __('app.no_description_available') }}
                    </div>
                    <div class="item-details">
                        <small>
                            <strong>{{ $contract->reference_number }}</strong> •
                            {{ $contract->contract_date ? $contract->contract_date->format('M j, Y') : __('app.date_not_available') }}
                        </small>
                    </div>
                </div>
                <div class="item-metric">
                    <div class="metric-primary">${{ number_format($contract->total_contract_value, 0) }}</div>
                    <div class="metric-secondary">{{ __('app.contract_value') }}</div>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
