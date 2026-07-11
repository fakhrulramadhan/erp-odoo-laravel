@extends('pdf.layout')

@section('content')
    <div class="report-header">
        <h1>{{ $title }}</h1>
        <div class="subtitle">Inventory Module Report</div>
        <div class="date">Period: {{ $period ?? 'All Time' }} | Generated: {{ now()->format('d M Y H:i') }}</div>
    </div>

    {{-- Summary Cards --}}
    @if(!empty($summary['totals']))
    <div class="summary-grid">
        <div class="summary-card">
            <div class="label">Stock Value</div>
            <div class="value">Rp {{ number_format($summary['totals']['total_value'] ?? 0, 0, ',', '.') }}</div>
        </div>
        <div class="summary-card">
            <div class="label">Total Quantity</div>
            <div class="value">{{ number_format($summary['totals']['total_quantity'] ?? 0, 0, ',', '.') }}</div>
        </div>
        <div class="summary-card">
            <div class="label">Products</div>
            <div class="value">{{ $summary['totals']['total_products'] ?? 0 }}</div>
        </div>
        <div class="summary-card">
            <div class="label">Low Stock</div>
            <div class="value" style="color: {{ ($summary['totals']['low_stock_count'] ?? 0) > 0 ? '#dc2626' : '#059669' }}">
                {{ $summary['totals']['low_stock_count'] ?? 0 }}
            </div>
        </div>
    </div>
    @endif

    {{-- Products by Type --}}
    @if(!empty($summary['by_type']))
    <div class="section-title">Products by Type</div>
    <table>
        <thead>
            <tr>
                <th>Type</th>
                <th class="text-center">Count</th>
            </tr>
        </thead>
        <tbody>
            @foreach($summary['by_type'] as $item)
            <tr>
                <td style="text-transform: capitalize;">{{ $item['type'] }}</td>
                <td class="text-center">{{ $item['count'] }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    {{-- Top Locations --}}
    @if(!empty($summary['top_locations']))
    <div class="section-title">Stock by Location</div>
    <table>
        <thead>
            <tr>
                <th>Location</th>
                <th class="text-right">Quantity</th>
                <th class="text-right">Value</th>
            </tr>
        </thead>
        <tbody>
            @foreach($summary['top_locations'] as $loc)
            <tr>
                <td>{{ $loc['location_name'] ?? 'Location #' . $loc['location_id'] }}</td>
                <td class="text-right">{{ number_format($loc['quantity'] ?? 0, 0, ',', '.') }}</td>
                <td class="text-right">Rp {{ number_format($loc['value'] ?? 0, 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    {{-- By Warehouse Detail --}}
    @if(!empty($byWarehouse))
    <div class="section-title">Stock Value by Location (Detail)</div>
    <table>
        <thead>
            <tr>
                <th>Location</th>
                <th class="text-center">Products</th>
                <th class="text-right">Total Qty</th>
                <th class="text-right">Reserved</th>
                <th class="text-right">Value</th>
            </tr>
        </thead>
        <tbody>
            @foreach($byWarehouse as $w)
            <tr>
                <td>{{ $w['location_name'] ?? 'Location #' . $w['location_id'] }}</td>
                <td class="text-center">{{ $w['product_count'] ?? 0 }}</td>
                <td class="text-right">{{ number_format($w['total_qty'] ?? 0, 0, ',', '.') }}</td>
                <td class="text-right">{{ number_format($w['reserved_qty'] ?? 0, 0, ',', '.') }}</td>
                <td class="text-right">Rp {{ number_format($w['total_value'] ?? 0, 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    {{-- Low Stock --}}
    @if(!empty($lowStock))
    <div class="section-title" style="color: #dc2626;">⚠ Low Stock Products (Need Restocking)</div>
    <table>
        <thead>
            <tr>
                <th>Code</th>
                <th>Product</th>
                <th class="text-right">On Hand</th>
                <th class="text-right">Min Stock</th>
                <th class="text-right text-red">Deficit</th>
                <th class="text-right">Restock Value</th>
            </tr>
        </thead>
        <tbody>
            @foreach($lowStock as $item)
            <tr>
                <td class="font-mono">{{ $item['code'] ?? '-' }}</td>
                <td>{{ $item['name'] ?? '-' }}</td>
                <td class="text-right">{{ number_format($item['on_hand'] ?? 0, 0, ',', '.') }}</td>
                <td class="text-right">{{ number_format($item['minimum_stock'] ?? 0, 0, ',', '.') }}</td>
                <td class="text-right text-red">-{{ number_format($item['deficit'] ?? 0, 0, ',', '.') }}</td>
                <td class="text-right">Rp {{ number_format($item['restock_value'] ?? 0, 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    {{-- Stock Value by Product --}}
    @if(!empty($stockValue))
    <div class="section-title">Stock Value by Product</div>
    <table>
        <thead>
            <tr>
                <th>Code</th>
                <th>Product</th>
                <th>Category</th>
                <th class="text-right">Quantity</th>
                <th class="text-right">Value</th>
            </tr>
        </thead>
        <tbody>
            @foreach($stockValue as $p)
            <tr>
                <td class="font-mono">{{ $p['code'] ?? '-' }}</td>
                <td>{{ $p['name'] ?? '-' }}</td>
                <td>{{ $p['category'] ?? '-' }}</td>
                <td class="text-right">{{ number_format($p['quantity'] ?? 0, 0, ',', '.') }}</td>
                <td class="text-right">Rp {{ number_format($p['total_value'] ?? 0, 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif
@endsection
