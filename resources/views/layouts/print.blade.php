{{-- resources/views/layouts/print.blade.php --}}
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'HYPER PSARO - Rapport')</title>
    
    <style>
        /* Reset et styles d'impression */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', 'Inter', 'Arial', sans-serif;
            background: white;
            color: #1a202c;
            line-height: 1.5;
            padding: 20px;
        }
        
        /* Conteneur principal */
        .print-container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
        }
        
        /* En-tête du rapport */
        .print-header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #1a56db;
        }
        
        .logo {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            margin-bottom: 15px;
        }
        
        .logo-icon {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, #1a56db, #7e3af2);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            color: white;
        }
        
        .logo-text {
            font-size: 24px;
            font-weight: 800;
            color: #1a56db;
        }
        
        .logo-text span {
            color: #7e3af2;
        }
        
        .report-title {
            font-size: 28px;
            font-weight: 800;
            margin: 15px 0 10px;
            color: #1a202c;
        }
        
        .report-subtitle {
            font-size: 14px;
            color: #718096;
        }
        
        .report-meta {
            margin-top: 15px;
            display: flex;
            justify-content: center;
            gap: 30px;
            font-size: 12px;
            color: #718096;
        }
        
        /* Sections */
        .print-section {
            margin-bottom: 30px;
            page-break-inside: avoid;
        }
        
        .section-title {
            font-size: 18px;
            font-weight: 700;
            color: #1a56db;
            margin-bottom: 15px;
            padding-left: 12px;
            border-left: 4px solid #1a56db;
        }
        
        /* Cartes d'information */
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .info-card {
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 15px;
            background: #f8fafc;
        }
        
        .info-card-title {
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #718096;
            margin-bottom: 8px;
        }
        
        .info-card-value {
            font-size: 28px;
            font-weight: 800;
            color: #1a202c;
        }
        
        .info-card-unit {
            font-size: 14px;
            color: #718096;
            margin-left: 5px;
        }
        
        /* Tableaux */
        .print-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        
        .print-table th {
            background: #1a56db;
            color: white;
            padding: 12px 15px;
            text-align: left;
            font-size: 13px;
            font-weight: 600;
        }
        
        .print-table td {
            padding: 10px 15px;
            border-bottom: 1px solid #e2e8f0;
            font-size: 13px;
        }
        
        .print-table tr:hover {
            background: #f8fafc;
        }
        
        /* Graphiques pour impression */
        .chart-container {
            margin: 20px 0;
            padding: 15px;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            background: white;
        }
        
        .chart-title {
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 15px;
            color: #1a202c;
        }
        
        /* Barres de progression pour impression */
        .progress-bar-container {
            margin: 10px 0;
        }
        
        .progress-label {
            display: flex;
            justify-content: space-between;
            font-size: 12px;
            margin-bottom: 5px;
        }
        
        .progress-bg {
            background: #e2e8f0;
            border-radius: 10px;
            height: 8px;
            overflow: hidden;
        }
        
        .progress-fill {
            background: linear-gradient(90deg, #1a56db, #7e3af2);
            height: 100%;
            border-radius: 10px;
        }
        
        /* Badges */
        .badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
        }
        
        .badge-success { background: #0e9f6e; color: white; }
        .badge-danger { background: #e02424; color: white; }
        .badge-warning { background: #f5a623; color: white; }
        .badge-info { background: #3abff8; color: white; }
        
        /* Pied de page */
        .print-footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #e2e8f0;
            text-align: center;
            font-size: 11px;
            color: #718096;
        }
        
        /* Pagination */
        @media print {
            body {
                padding: 0;
                margin: 0;
            }
            
            .print-container {
                max-width: 100%;
                padding: 20px;
            }
            
            .print-section {
                page-break-inside: avoid;
            }
            
            .print-table {
                page-break-inside: avoid;
            }
            
            .no-print {
                display: none;
            }
            
            @page {
                size: A4;
                margin: 2cm;
            }
        }
        
        /* Page break */
        .page-break {
            page-break-before: always;
        }
        
        /* Signature */
        .signature-section {
            margin-top: 50px;
            display: flex;
            justify-content: space-between;
        }
        
        .signature-box {
            text-align: center;
            width: 200px;
        }
        
        .signature-line {
            border-top: 1px solid #1a202c;
            margin-top: 40px;
            padding-top: 10px;
            font-size: 12px;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .info-grid {
                grid-template-columns: 1fr;
            }
            
            .print-table {
                font-size: 11px;
            }
            
            .print-table th,
            .print-table td {
                padding: 8px 10px;
            }
        }
    </style>
    
    @stack('styles')
</head>
<body>
    <div class="print-container">
        
        <!-- En-tête -->
        <div class="print-header">
            <div class="logo">
                <div class="logo-icon">
                    <i class="fas fa-chart-line"></i>
                </div>
                <div class="logo-text">
                    HYPER <span>PSARO</span>
                </div>
            </div>
            <div class="report-title">@yield('report-title', 'Rapport Financier')</div>
            <div class="report-subtitle">@yield('report-subtitle', 'Système Décisionnel Power BI')</div>
            <div class="report-meta">
                <span><i class="fas fa-calendar"></i> Date : {{ date('d/m/Y') }}</span>
                <span><i class="fas fa-clock"></i> Heure : {{ date('H:i') }}</span>
                <span><i class="fas fa-user"></i> Généré par : {{ Auth::user()->nom ?? 'Système' }}</span>
            </div>
        </div>
        
        <!-- Contenu principal -->
        @yield('content')
        
        <!-- Signature -->
        @hasSection('signature')
            <div class="signature-section">
                <div class="signature-box">
                    <div class="signature-line">
                        Signature Direction Générale
                    </div>
                </div>
                <div class="signature-box">
                    <div class="signature-line">
                        Signature Conseiller Financier
                    </div>
                </div>
            </div>
        @endif
        
        <!-- Pied de page -->
        <div class="print-footer">
            <p>
                <i class="fas fa-chart-line"></i> HYPER PSARO - Système Décisionnel Power BI |
                Tous droits réservés &copy; {{ date('Y') }} |
                Document généré automatiquement
            </p>
            <p style="margin-top: 5px;">
                Ce rapport est confidentiel et destiné à l'usage interne de HYPER PSARO.
            </p>
        </div>
        
    </div>
    
    <!-- Script pour l'impression automatique -->
    @hasSection('auto-print')
    <script>
        window.onload = function() {
            window.print();
        }
    </script>
    @endif
    
    @stack('scripts')
</body>
</html>