{{-- resources/views/direction/reports/pdf/annual-report.blade.php --}}
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>{{ $title }} - {{ $year }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'DejaVu Sans', Arial, sans-serif; font-size: 12px; color: #1e293b; }
        .container { padding: 20px; }
        .header { text-align: center; margin-bottom: 30px; padding-bottom: 20px; border-bottom: 2px solid #3abff8; }
        .logo { font-size: 24px; font-weight: bold; color: #3abff8; margin-bottom: 10px; }
        .logo span { color: #F2C811; }
        .title { font-size: 20px; font-weight: bold; margin: 10px 0; }
        .subtitle { color: #64748b; font-size: 11px; }
        .report-info { display: flex; justify-content: space-between; margin: 20px 0; padding: 10px; background: #f8fafc; border-radius: 8px; }
        .kpi-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 15px; margin: 20px 0; }
        .kpi-card { background: white; border: 1px solid #e2e8f0; border-radius: 12px; padding: 15px; text-align: center; }
        .kpi-value { font-size: 24px; font-weight: bold; color: #3abff8; }
        .kpi-label { font-size: 11px; color: #64748b; margin-top: 5px; }
        .table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        .table th { background: #3abff8; color: white; padding: 10px; text-align: left; font-size: 11px; }
        .table td { padding: 8px 10px; border-bottom: 1px solid #e2e8f0; }
        .footer { margin-top: 30px; padding-top: 20px; border-top: 1px solid #e2e8f0; text-align: center; font-size: 10px; color: #94a3b8; }
        .text-success { color: #0e9f6e; }
        .text-danger { color: #e02424; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo">HYPER <span>PSARO</span></div>
            <div class="title">{{ $title }}</div>
            <div class="subtitle">Exercice {{ $year }}</div>
        </div>
        
        <div class="report-info">
            <div>Date : {{ $generated_at->translatedFormat('d/m/Y H:i') }}</div>
            <div>Généré par : {{ $generated_by }}</div>
        </div>
        
        <div class="kpi-grid">
            <div class="kpi-card">
                <div class="kpi-value">{{ number_format($total_ca ?? 0, 0, ',', ' ') }}</div>
                <div class="kpi-label">CA Total (USD)</div>
            </div>
            <div class="kpi-card">
                <div class="kpi-value" style="color: #f5a623;">{{ count($best_months ?? []) }}</div>
                <div class="kpi-label">Meilleurs mois</div>
            </div>
        </div>
        
        <h3>Évolution mensuelle</h3>
        <table class="table">
            <thead>
                <tr>
                    <th>Mois</th>
                    @foreach($months ?? [] as $m)
                        <th>{{ $m }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><strong>{{ $year }}</strong></td>
                    @foreach($monthly_data ?? [] as $val)
                        <td>{{ number_format($val, 0, ',', ' ') }}</td>
                    @endforeach
                </tr>
                <tr>
                    <td><strong>{{ $year - 1 }}</strong></td>
                    @foreach($prev_year_data ?? [] as $val)
                        <td>{{ number_format($val, 0, ',', ' ') }}</td>
                    @endforeach
                </tr>
            </tbody>
        </table>
        
        <h3>Top 3 meilleurs mois</h3>
        <table class="table">
            <thead>
                <tr><th>Rang</th><th>Mois</th><th>CA (USD)</th></tr>
            </thead>
            <tbody>
                @forelse($best_months ?? [] as $index => $month)
                <tr>
                    <td><strong>#{{ $index + 1 }}</strong></td>
                    <td>{{ $month['month'] }}</td>
                    <td>{{ number_format($month['value'], 0, ',', ' ') }}</td>
                </tr>
                @empty
                <tr><td colspan="3" style="text-align: center; color: #94a3b8;">Aucune donnée</td></tr>
                @endforelse
            </tbody>
        </table>
        
        <div class="footer">
            <p>Document confidentiel - HYPER PSARO - Tous droits réservés</p>
        </div>
    </div>
</body>
</html>