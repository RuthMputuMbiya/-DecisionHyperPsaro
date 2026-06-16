<?php
// app/Http/Controllers/Finance/ReportingController.php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\FinanceFait;
use App\Models\Indicateur;
use App\Models\Decision;
use App\Models\Utilisateur;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class ReportingController extends Controller
{
    // ... (gardez votre code index, etc.)

    /**
     * Exporter les données (PDF, Excel, CSV)
     */
    public function export(Request $request)
    {
        $request->validate([
            'type' => 'required|in:pdf,excel,csv',
            'report_type' => 'required|in:financial,kpi,services,decisions,monthly,yearly,custom',
            'format' => 'required|in:A4,Letter,Legal',
            'orientation' => 'required|in:portrait,landscape',
            'include_charts' => 'boolean',
            'include_summary' => 'boolean',
            'period' => 'nullable|string',
            'year' => 'nullable|integer',
            'month' => 'nullable|integer',
            'service_id' => 'nullable|integer',
            'indicateur_id' => 'nullable|integer',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
        ]);
        
        $devise = session('devise', 'USD');
        $data = $this->getReportData($request);
        
        // Incrémenter les compteurs
        session(['total_exports' => session('total_exports', 0) + 1]);
        if ($request->type == 'pdf') {
            session(['pdf_count' => session('pdf_count', 0) + 1]);
        } else {
            session(['excel_count' => session('excel_count', 0) + 1]);
        }
        session(['last_export' => now()]);
        
        // Générer le fichier selon le type
        switch ($request->type) {
            case 'pdf':
                return $this->exportPDF($data, $request);
            case 'excel':
                // 🔥 REDIRIGER VERS CSV SI EXCEL N'EST PAS DISPONIBLE
                return $this->exportCSV($data, $request);
            case 'csv':
                return $this->exportCSV($data, $request);
            default:
                return back()->with('error', 'Format non supporté');
        }
    }
    
    /**
     * Export PDF
     */
    private function exportPDF($data, $request)
    {
        $pdf = PDF::loadView('finance.reporting.exports.pdf', $data);
        $pdf->setPaper($request->format, $request->orientation);
        
        $filename = 'rapport_' . $data['report_type'] . '_' . date('Y-m-d_His') . '.pdf';
        return $pdf->download($filename);
    }
    
    /**
     * 🔥 EXPORT CSV (SANS EXCEL)
     */
    private function exportCSV($data, $request)
    {
        $filename = 'export_' . $data['report_type'] . '_' . date('Y-m-d_His') . '.csv';
        
        $handle = fopen('php://temp', 'w+');
        
        // Ajouter BOM pour UTF-8
        fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));
        
        // En-têtes
        $headers = $this->getCSVHeaders($data['report_type']);
        fputcsv($handle, $headers, ';');
        
        // Données
        foreach ($this->getCSVData($data) as $row) {
            fputcsv($handle, $row, ';');
        }
        
        rewind($handle);
        $content = stream_get_contents($handle);
        fclose($handle);
        
        return response($content, 200)
            ->header('Content-Type', 'text/csv; charset=utf-8')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }
    
    // ... (gardez toutes vos autres méthodes)
}