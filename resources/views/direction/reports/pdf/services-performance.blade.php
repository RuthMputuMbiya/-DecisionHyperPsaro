{{-- resources/views/direction/reports/pdf/services-performance.blade.php --}}
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>{{ $title ?? 'Rapport' }} - HYPER PSARO</title>
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
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e2e8f0;
            text-align: center;
            font-size: 10px;
            color: #94a3b8;
        }
        .badge-success {
            color: #0e9f6e;
        }
        .badge-danger {
            color: #e02424;
        }
    </style>
</head>
<body>
    <div class="container">
        
        {{-- En-tête --}}
        <div class="header">
            <div class="logo">HYPER <span>PSARO</span></div>
            <div class="title">{{ $title ?? 'Rapport Performance par Service' }}</div>
            <div class="subtitle">Exercice {{ $year ?? date('Y') }}</div>
        </div>
        
        {{-- Informations --}}
        <div class="report-info">
            <div>Date de génération : {{ $generated_at->translatedFormat('d/m/Y H:i') }}</div>
            <div>Généré par : {{ $generated_by ?? 'Système' }}</div>
            <div>Devise : {{ $devise ?? 'USD' }}</div>
        </div>
        
        {{-- KPIs --}}
        <div style="margin: 20px 0;">
            <div style="display: flex; justify-content: space-between; margin-bottom: 15px;">
                <div style="flex: 1; text-align: center; padding: 10px; background: #f8fafc; margin: 0 5px; border-radius: 8px;">
                    <div style="font-size: 11px; color: #64748b;">Total CA</div>
                    <div style="font-size: 18px; font-weight: bold; color: #1B6EF3;">{{ number_format($total_ca ?? 0, 0, ',', ' ') }} {{ $devise ?? 'USD' }}</div>
                </div>
                <div style="flex: 1; text-align: center; padding: 10px; background: #f8fafc; margin: 0 5px; border-radius: 8px;">
                    <div style="font-size: 11px; color: #64748b;">Total Marge</div>
                    <div style="font-size: 18px; font-weight: bold; color: #0e9f6e;">{{ number_format($total_marge ?? 0, 0, ',', ' ') }} {{ $devise ?? 'USD' }}</div>
                </div>
                <div style="flex: 1; text-align: center; padding: 10px; background: #f8fafc; margin: 0 5px; border-radius: 8px;">
                    <div style="font-size: 11px; color: #64748b;">Services actifs</div>
                    <div style="font-size: 18px; font-weight: bold; color: #f5a623;">{{ $services->count() ?? 0 }}</div>
                </div>
            </div>
        </div>
        
        {{-- Tableau des services --}}
        <table class="table">
            <thead>
                <tr>
                    <th>Service</th>
                    <th>Chiffre d'Affaires</th>
                    <th>Marge</th>
                    <th>Performance</th>
                    <th>Employés</th>
                </tr>
            </thead>
            <tbody>
                @forelse($services ?? [] as $service)
                <tr>
                    <td><strong>{{ $service->nom_service ?? '-' }}</strong></td>
                    <td>{{ number_format($service->ca ?? 0, 0, ',', ' ') }} {{ $devise ?? 'USD' }}</td>
                    <td>{{ number_format($service->marge ?? 0, 0, ',', ' ') }} {{ $devise ?? 'USD' }}</td>
                    <td class="{{ ($service->performance ?? 0) >= 20 ? 'badge-success' : 'badge-danger' }}">{{ $service->performance ?? 0 }}%</td>
                    <td>{{ $service->employes ?? 0 }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" style="text-align: center;">Aucune donnée disponible</td>
                </tr>
                @endforelse
            </tbody>
        </table>
        
        {{-- Pied de page --}}
        <div class="footer">
            <p>Document confidentiel - HYPER PSARO - Tous droits réservés</p>
            <p>Ce rapport est généré automatiquement par le système décisionnel</p>
        </div>
        
    </div>
</body>
</html>