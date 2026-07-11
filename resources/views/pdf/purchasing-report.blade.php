@extends('pdf.layout')

@section('content')
    <div class="report-header">
        <h1>{{ $title }}</h1>
        <div class="subtitle">Purchasing Module Report</div>
        <div class="date">Period: {{ $period ?? 'All Time' }} | Generated: {{ now()->format('d M Y H:i') }}</div>
    </div>

    {{-- Summary Cards --}}
    @if(!empty($summary['totals']))
    <div class="summary-grid">
        <div class="summary-card">
            <div class="label">Total Orders</div>
            <div class="value">{{ $summary['totals']['total_orders'] ?? 0 }}</div>
        </div>
        <div class="summary-card">
            <div class="label">Total Value</div>
            <div class="value">Rp {{ number_format($summary['totals']['total_value'] ?? 0, 0, ',', '.') }}</div>
        </div>
        <div class="summary-card">
            <div class="label">Unique Vendors</div>
            <div class="value">{{ $summary['totals']['unique_vendors'] ?? 0 }}</div>
        </div>
        <div class="summary-card">
            <div class="label">Report Type</div>
            <div class="value" style="font-size:14px;">{{ ucfirst($type ?? 'Summary') }}</div>
        </div>
    </div>
    @endif

    {{-- Orders by Status --}}
    @if(!empty($summary['by_status']))
    <div class="section-title">Orders by Status</div>
    <table>
        <thead>
            <tr>
                <th>Status</th>
                <th class="text-center">Count</th>
                <th class="text-right">Total Value</th>
            </tr>
        </thead>
        <tbody>
            @foreach($summary['by_status'] as $status)
            <tr>
                <td style="text-transform: capitalize;">{{ $status['status']->label() }}</td>
                <td class="text-center">{{ $status['count'] }}</td>
                <td class="text-right">Rp {{ number_format($status['total_value'] ?? 0, 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    {{-- Top Vendors --}}
    @if(!empty($summary['top_vendors']))
    <div class="section-title">Top Vendors by Spend</div>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Vendor</th>
                <th class="text-center">Orders</th>
                <th class="text-right">Total Spent</th>
            </tr>
        </thead>
        <tbody>
            @foreach($summary['top_vendors'] as $i => $vendor)
            <tr>
                <td class="text-center">{{ $i + 1 }}</td>
                <td>{{ $vendor['vendor_name'] }}</td>
                <td class="text-center">{{ $vendor['orders'] }}</td>
                <td class="text-right">Rp {{ number_format($vendor['total_spent'] ?? 0, 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    {{-- By Vendor Detail --}}
    @if(!empty($byVendor))
    <div class="section-title">Purchasing by Vendor (Detail)</div>
    <table>
        <thead>
            <tr>
                <th>Vendor</th>
                <th class="text-center">Orders</th>
                <th class="text-right">Total Spent</th>
                <th class="text-center">Draft</th>
                <th class="text-center">Approved</th>
                <th class="text-center">Active</th>
            </tr>
        </thead>
        <tbody>
            @foreach($byVendor as $v)
            <tr>
                <td>{{ $v['vendor_name'] }}</td>
                <td class="text-center">{{ $v['orders'] }}</td>
                <td class="text-right">Rp {{ number_format($v['total_spent'] ?? 0, 0, ',', '.') }}</td>
                <td class="text-center">{{ $v['draft'] ?? 0 }}</td>
                <td class="text-center">{{ $v['approved'] ?? 0 }}</td>
                <td class="text-center">{{ $v['active'] ?? 0 }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    {{-- Monthly Trend --}}
    @if(!empty($byMonth))
    <div class="section-title">Monthly Purchasing Trend</div>
    <table>
        <thead>
            <tr>
                <th>Month</th>
                <th class="text-center">Orders</th>
                <th class="text-right">Total Value</th>
            </tr>
        </thead>
        <tbody>
            @foreach($byMonth as $m)
            <tr>
                <td>{{ $m['month_name'] }}</td>
                <td class="text-center">{{ $m['orders'] }}</td>
                <td class="text-right">Rp {{ number_format($m['total_value'] ?? 0, 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    {{-- Top Products --}}
    @if(!empty($topProducts))
    <div class="section-title">Top Purchased Products</div>
    <table>
        <thead>
            <tr>
                <th>Code</th>
                <th>Product</th>
                <th class="text-right">Total Qty</th>
                <th class="text-right">Total Amount</th>
                <th class="text-center">Orders</th>
            </tr>
        </thead>
        <tbody>
            @foreach($topProducts as $p)
            <tr>
                <td class="font-mono">{{ $p['product_code'] ?? $p['code'] ?? '-' }}</td>
                <td>{{ $p['product_name'] ?? $p['name'] ?? '-' }}</td>
                <td class="text-right">{{ number_format($p['total_qty'] ?? 0, 0, ',', '.') }}</td>
                <td class="text-right">Rp {{ number_format($p['total_amount'] ?? 0, 0, ',', '.') }}</td>
                <td class="text-center">{{ $p['order_count'] ?? 0 }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif
@endsection
