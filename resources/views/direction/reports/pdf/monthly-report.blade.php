{{-- resources/views/direction/reports/pdf/monthly-report.blade.php --}}
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>{{ $title }} - {{ $month_name }} {{ $year }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'DejaVu Sans', Arial, sans-serif; font-size: 12px; color: #1e293b; }
        .container { padding: 20px; }
        .header { text-align: center; margin-bottom: 30px; padding-bottom: 20px; border-bottom: 2px solid #e02424; }
        .logo { font-size: 24px; font-weight: bold; color: #e02424; margin-bottom: 10px; }
        .logo span { color: #F2C811; }
        .title { font-size: 20px; font-weight: bold; margin: 10px 0; }
        .subtitle { color: #64748b; font-size: 11px; }
        .report-info { display: flex; justify-content: space-between; margin: 20px 0; padding: 10px; background: #f8fafc; border-radius: 8px; }
        .kpi-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 15px; margin: 20px 0; }
        .kpi-card { background: white; border: 1px solid #e2e8f0; border-radius: 12px; padding: 15px; text-align: center; }
        .kpi-value { font-size: 24px; font-weight: bold; color: #e02424; }
        .kpi-label { font-size: 11px; color: #64748b; margin-top: 5px; }
        .table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        .table th { background: #e02424; color: white; padding: 10px; text-align: left; font-size: 11px; }
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
            <div class="subtitle">{{ $month_name }} {{ $year }}</div>
        </div>
        
        <div class="report-info">
            <div>Date : {{ $generated_at->translatedFormat('d/m/Y H:i') }}</div>
            <div>Généré par : {{ $generated_by }}</div>
        </div>
        
        <div class="kpi-grid">
            <div class="kpi-card">
                <div class="kpi-value">{{ number_format($ca ?? 0, 0, ',', ' ') }}</div>
                <div class="kpi-label">CA du mois (USD)</div>
            </div>
            <div class="kpi-card">
                <div class="kpi-value" style="color: {{ ($croissance ?? 0) >= 0 ? '#0e9f6e' : '#e02424' }}">
                    {{ ($croissance ?? 0) >= 0 ? '+' : '' }}{{ $croissance ?? 0 }}%
                </div>
                <div class="kpi-label">Croissance</div>
            </div>
            <div class="kpi-card">
                <div class="kpi-value" style="color: #f5a623;">{{ number_format($ca_precedent ?? 0, 0, ',', ' ') }}</div>
                <div class="kpi-label">CA mois précédent (USD)</div>
            </div>
        </div>
        
        <h3>Performance par service</h3>
        <table class="table">
            <thead>
                <tr><th>Service</th><th>CA (USD)</th><th>Contribution</th></tr>
            </thead>
            <tbody>
                @forelse($services ?? [] as $service)
                <tr>
                    <td><strong>{{ $service->nom_service }}</strong></td>
                    <td>{{ number_format($service->ca ?? 0, 0, ',', ' ') }}</td>
                    <td>{{ $ca > 0 ? round(($service->ca / $ca) * 100, 1) : 0 }}%</td>
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