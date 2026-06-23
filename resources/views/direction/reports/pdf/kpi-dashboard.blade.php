{{-- resources/views/direction/reports/pdf/kpi-dashboard.blade.php --}}
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>{{ $title }} - {{ $year }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'DejaVu Sans', Arial, sans-serif; font-size: 12px; color: #1e293b; }
        .container { padding: 20px; }
        .header { text-align: center; margin-bottom: 30px; padding-bottom: 20px; border-bottom: 2px solid #0e9f6e; }
        .logo { font-size: 24px; font-weight: bold; color: #0e9f6e; margin-bottom: 10px; }
        .logo span { color: #F2C811; }
        .title { font-size: 20px; font-weight: bold; margin: 10px 0; }
        .subtitle { color: #64748b; font-size: 11px; }
        .report-info { display: flex; justify-content: space-between; margin: 20px 0; padding: 10px; background: #f8fafc; border-radius: 8px; }
        .kpi-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 15px; margin: 20px 0; }
        .kpi-card { background: white; border: 1px solid #e2e8f0; border-radius: 12px; padding: 15px; text-align: center; }
        .kpi-value { font-size: 24px; font-weight: bold; color: #0e9f6e; }
        .kpi-label { font-size: 11px; color: #64748b; margin-top: 5px; }
        .table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        .table th { background: #0e9f6e; color: white; padding: 10px; text-align: left; font-size: 11px; }
        .table td { padding: 8px 10px; border-bottom: 1px solid #e2e8f0; }
        .badge { display: inline-block; padding: 3px 8px; border-radius: 20px; font-size: 10px; font-weight: bold; }
        .badge-success { background: #0e9f6e20; color: #0e9f6e; }
        .badge-danger { background: #e0242420; color: #e02424; }
        .footer { margin-top: 30px; padding-top: 20px; border-top: 1px solid #e2e8f0; text-align: center; font-size: 10px; color: #94a3b8; }
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
                <div class="kpi-value">{{ $total_kpis ?? 0 }}</div>
                <div class="kpi-label">Total KPIs</div>
            </div>
            <div class="kpi-card">
                <div class="kpi-value" style="color: #e02424;">{{ $alertes_count ?? 0 }}</div>
                <div class="kpi-label">Alertes</div>
            </div>
        </div>
        
        <h3>Liste des indicateurs</h3>
        <table class="table">
            <thead>
                <tr><th>Code</th><th>Indicateur</th><th>Valeur</th><th>Unité</th><th>Statut</th></tr>
            </thead>
            <tbody>
                @forelse($indicateurs ?? [] as $ind)
                <tr>
                    <td><strong>{{ $ind->code_kpi }}</strong></td>
                    <td>{{ $ind->nom_kpi }}</td>
                    <td>{{ number_format($ind->valeur ?? 0, 0, ',', ' ') }}</td>
                    <td>{{ $ind->unite }}</td>
                    <td>
                        @if($ind->est_alerte ?? false)
                            <span class="badge badge-danger">⚠️ Alerte</span>
                        @else
                            <span class="badge badge-success">✓ OK</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="5" style="text-align: center; color: #94a3b8;">Aucun indicateur trouvé</td></tr>
                @endforelse
            </tbody>
        </table>
        
        <div class="footer">
            <p>Document confidentiel - HYPER PSARO - Tous droits réservés</p>
        </div>
    </div>
</body>
</html>