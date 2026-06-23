{{-- resources/views/direction/reports/pdf/decisions-history.blade.php --}}
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
            border-bottom: 2px solid #7e3af2;
        }
        .logo {
            font-size: 24px;
            font-weight: bold;
            color: #7e3af2;
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
            color: #7e3af2;
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
            background: #7e3af2;
            color: white;
            padding: 10px;
            text-align: left;
            font-size: 11px;
        }
        .table td {
            padding: 8px 10px;
            border-bottom: 1px solid #e2e8f0;
        }
        .table tr:hover {
            background: #f8fafc;
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
        .badge-info { background: #3abff820; color: #3abff8; }
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e2e8f0;
            text-align: center;
            font-size: 10px;
            color: #94a3b8;
        }
        .text-success { color: #0e9f6e; }
        .text-danger { color: #e02424; }
        .text-warning { color: #f5a623; }
        .text-info { color: #3abff8; }
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
        
        {{-- KPIs --}}
        <div class="kpi-grid">
            <div class="kpi-card">
                <div class="kpi-value">{{ $stats['total'] ?? 0 }}</div>
                <div class="kpi-label">Total décisions</div>
            </div>
            <div class="kpi-card">
                <div class="kpi-value" style="color: #0e9f6e;">{{ $stats['validees'] ?? 0 }}</div>
                <div class="kpi-label">Validées</div>
            </div>
            <div class="kpi-card">
                <div class="kpi-value" style="color: #e02424;">{{ $stats['rejetees'] ?? 0 }}</div>
                <div class="kpi-label">Rejetées</div>
            </div>
            <div class="kpi-card">
                <div class="kpi-value" style="color: #f5a623;">{{ $stats['taux_validation'] ?? 0 }}%</div>
                <div class="kpi-label">Taux de validation</div>
            </div>
        </div>
        
        {{-- Liste des décisions --}}
        <h3 style="margin: 20px 0 10px;">Historique des décisions</h3>
        @if(isset($decisions) && $decisions->count() > 0)
        <table class="table">
            <thead>
                <tr>
                    <th>Réf.</th>
                    <th>Titre</th>
                    <th>Impact</th>
                    <th>Statut</th>
                    <th>Date</th>
                    <th>Créé par</th>
                </tr>
            </thead>
            <tbody>
                @foreach($decisions as $decision)
                <tr>
                    <td><strong>#{{ str_pad($decision->id_decision, 5, '0', STR_PAD_LEFT) }}</strong></td>
                    <td>{{ Str::limit($decision->titre, 40) }}</td>
                    <td class="{{ ($decision->impact_tresorerie ?? 0) >= 0 ? 'text-success' : 'text-danger' }}">
                        {{ ($decision->impact_tresorerie ?? 0) >= 0 ? '+' : '' }}
                        {{ number_format($decision->impact_tresorerie ?? 0, 0, ',', ' ') }} USD
                    </td>
                    <td>
                        @php
                            $statusBadge = [
                                'En attente' => 'badge-warning',
                                'Validée' => 'badge-success',
                                'Rejetée' => 'badge-danger',
                                'En cours' => 'badge-info'
                            ];
                        @endphp
                        <span class="badge {{ $statusBadge[$decision->statut] ?? 'badge-info' }}">
                            {{ $decision->statut ?? 'En attente' }}
                        </span>
                    </td>
                    <td>{{ $decision->created_at->translatedFormat('d/m/Y') }}</td>
                    <td>{{ $decision->createur->nom ?? 'Système' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @else
        <p style="text-align: center; padding: 30px; color: #94a3b8;">Aucune décision enregistrée pour l'année {{ $year }}</p>
        @endif
        
        {{-- Pied de page --}}
        <div class="footer">
            <p>Document confidentiel - HYPER PSARO - Tous droits réservés</p>
        </div>
        
    </div>
</body>
</html>