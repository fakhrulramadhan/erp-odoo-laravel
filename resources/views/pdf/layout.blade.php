<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ $title ?? 'FLTech ERP Report' }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 10px;
            color: #1a1a1a;
            line-height: 1.5;
        }

        /* Header */
        .report-header {
            border-bottom: 3px solid #4F46E5;
            padding-bottom: 12px;
            margin-bottom: 20px;
        }
        .report-header h1 {
            font-size: 22px;
            color: #4F46E5;
            margin-bottom: 4px;
        }
        .report-header .subtitle {
            font-size: 11px;
            color: #6b7280;
        }
        .report-header .date {
            font-size: 10px;
            color: #9ca3af;
            margin-top: 4px;
        }

        /* Summary cards */
        .summary-grid {
            display: table;
            width: 100%;
            margin-bottom: 20px;
        }
        .summary-card {
            display: table-cell;
            width: 25%;
            padding: 10px 12px;
            background: #f3f4f6;
            border: 1px solid #e5e7eb;
            text-align: center;
        }
        .summary-card .label {
            font-size: 9px;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .summary-card .value {
            font-size: 18px;
            font-weight: bold;
            color: #4F46E5;
            margin-top: 2px;
        }

        /* Section titles */
        .section-title {
            font-size: 14px;
            font-weight: bold;
            color: #1f2937;
            margin: 20px 0 8px 0;
            padding-bottom: 4px;
            border-bottom: 1px solid #e5e7eb;
        }

        /* Tables */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 16px;
            font-size: 9px;
        }
        thead th {
            background: #4F46E5;
            color: white;
            padding: 8px 10px;
            text-align: left;
            font-weight: 600;
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }
        tbody td {
            padding: 7px 10px;
            border-bottom: 1px solid #e5e7eb;
        }
        tbody tr:nth-child(even) {
            background: #f9fafb;
        }
        tbody tr:hover {
            background: #f3f4f6;
        }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .font-mono { font-family: 'Courier New', monospace; }
        .text-red { color: #dc2626; font-weight: 600; }

        /* Footer */
        .report-footer {
            margin-top: 30px;
            padding-top: 10px;
            border-top: 1px solid #e5e7eb;
            font-size: 8px;
            color: #9ca3af;
            text-align: center;
        }

        /* Page break */
        .page-break { page-break-before: always; }
    </style>
</head>
<body>
    @yield('content')

    <div class="report-footer">
        FLTech ERP — {{ $title ?? 'Report' }} — Generated {{ now()->format('d M Y H:i') }} — Page {PAGE_NUM} of {PAGE_COUNT}
    </div>
</body>
</html>
