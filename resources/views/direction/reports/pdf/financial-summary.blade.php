{{-- resources/views/direction/reports/pdf/financial-summary.blade.php --}}
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>{{ $title }} - {{ $year }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'DejaVu Sans', 'Segoe UI', Arial, sans-serif;
            font-size: 12px;
            line-height: 1.5;
            color: #1e293b;
        }
        .container {
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #1B6EF3;
        }
        .logo {
            font-size: 24px;
            font-weight: bold;
            color: #1B6EF3;
            margin-bottom: 10px;
        }
        .logo span {
            color: #F2C811;
        }
        .title {
            font-size: 20px;
            font-weight: bold;
            margin: 10px 0;
        }
        .subtitle {
            color: #64748b;
            font-size: 11px;
        }
        .report-info {
            display: flex;
            justify-content: space-between;
            margin: 20px 0;
            padding: 10px;
            background: #f8fafc;
            border-radius: 8px;
        }
        .kpi-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 15px;
            margin: 20px 0;
        }
        .kpi-card {
            background: white;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 15px;
            text-align: center;
        }
        .kpi-value {
            font-size: 24px;
            font-weight: bold;
            color: #1B6EF3;
        }
        .kpi-label {
            font-size: 11px;
            color: #64748b;
            margin-top: 5px;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        .table th {
            background: #1B6EF3;
            color: white;
            padding: 10px;
            text-align: left;
            font-size: 11px;
        }
        .table td {
            padding: 8px 10px;
            border-bottom: 1px solid #e2e8f0;
        }
        .chart-container {
            margin: 20px 0;
            text-align: center;
        }
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e2e8f0;
            text-align: center;
            font-size: 10px;
            color: #94a3b8;
        }
        .page-break {
            page-break-before: always;
        }
        .badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 20px;
            font-size: 10px;
            font-weight: bold;
        }
        .badge-success { background: #0e9f6e20; color: #0e9f6e; }
        .badge-danger { background: #e0242420; color: #e02424; }
        .badge-warning { background: #f5a62320; color: #f5a623; }
    </style>
</head>
<body>
    <div class="container">
        
        {{-- En-tête --}}
        <div class="header">
            <div class="logo">HYPER <span>PSARO</span></div>
            <div class="title">{{ $title }}</div>
            <div class="subtitle">Exercice {{ $year }}</div>
        </div>
        
        {{-- Informations --}}
        <div class="report-info">
            <div>Date de génération : {{ $generated_at->translatedFormat('d/m/Y H:i') }}</div>
            <div>Généré par : {{ $generated_by }}</div>
        </div>
        
        {{-- KPIs principaux --}}
        <div class="kpi-grid">
            <div class="kpi-card">
                <div class="kpi-value">{{ number_format($ca_total, 0, ',', ' ') }}</div>
                <div class="kpi-label">Chiffre d'affaires (USD)</div>
            </div>
            <div class="kpi-card">
                <div class="kpi-value">{{ number_format($marge_total, 0, ',', ' ') }}</div>
                <div class="kpi-label">Marge brute (USD)</div>
            </div>
            <div class="kpi-card">
                <div class="kpi-value">{{ number_format($taux_marge, 1) }}%</div>
                <div class="kpi-label">Taux de marge</div>
            </div>
            <div class="kpi-card">
                <div class="kpi-value">{{ number_format($tresorerie, 0, ',', ' ') }}</div>
                <div class="kpi-label">Trésorerie (USD)</div>
            </div>
        </div>
        
        {{-- Évolution mensuelle --}}
        <h3 style="margin: 20px 0 10px;">Évolution mensuelle du CA</h3>
        <table class="table">
            <thead>
                <tr>
                    <th>Mois</th>
                    @foreach($months as $month)
                        <th>{{ $month }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><strong>CA (USD)</strong></td>
                    @foreach($monthly_data as $value)
                        <td>{{ number_format($value, 0, ',', ' ') }}</td>
                    @endforeach
                </tr>
            </tbody>
        </table>
        
        {{-- Top services --}}
        <h3 style="margin: 20px 0 10px;">Top 5 services performants</h3>
        <table class="table">
            <thead>
                <tr><th>Service</th><th>Chiffre d'affaires</th><th>Contribution</th></tr>
            </thead>
            <tbody>
                @foreach($top_services as $service)
                <tr>
                    <td>{{ $service->nom_service }}</td>
                    <td>{{ number_format($service->ca, 0, ',', ' ') }} USD</td>
                    <td>{{ $ca_total > 0 ? round(($service->ca / $ca_total) * 100, 1) : 0 }}%</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        
        {{-- Pied de page --}}
        <div class="footer">
            <p>Document confidentiel - HYPER PSARO - Tous droits réservés</p>
        </div>
        
    </div>
</body>
</html>