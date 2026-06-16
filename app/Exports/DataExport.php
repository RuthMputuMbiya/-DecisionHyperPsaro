<?php
// app/Exports/DataExport.php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class DataExport implements FromArray, WithHeadings, WithStyles
{
    protected $data;
    
    public function __construct($data)
    {
        $this->data = $data;
    }
    
    public function array(): array
    {
        $rows = [];
        
        if (isset($this->data['services'])) {
            foreach ($this->data['services'] as $service) {
                $rows[] = [
                    $service->nom_service,
                    $service->ca,
                    $service->marge,
                    $service->performance . '%',
                    $service->employes
                ];
            }
        } elseif (isset($this->data['indicateurs'])) {
            foreach ($this->data['indicateurs'] as $ind) {
                $rows[] = [
                    $ind->nom_kpi,
                    $ind->code_kpi,
                    $ind->valeur,
                    $ind->unite,
                    $ind->est_alerte ? 'Alerte' : 'Normal'
                ];
            }
        } elseif (isset($this->data['decisions'])) {
            foreach ($this->data['decisions'] as $decision) {
                $rows[] = [
                    $decision->titre,
                    $decision->statut,
                    $decision->impact_tresorerie,
                    $decision->created_at->format('d/m/Y'),
                    $decision->createur->nom ?? '-'
                ];
            }
        } elseif (isset($this->data['monthly_data'])) {
            foreach ($this->data['monthly_data'] as $index => $value) {
                $rows[] = [
                    $this->data['months'][$index],
                    $value,
                    $this->data['marge_total'] ?? 0,
                    $this->data['taux_marge'] ?? 0
                ];
            }
        }
        
        return $rows;
    }
    
    public function headings(): array
    {
        if (isset($this->data['services'])) {
            return ['Service', 'Chiffre d\'affaires', 'Marge', 'Performance', 'Employés'];
        } elseif (isset($this->data['indicateurs'])) {
            return ['Indicateur', 'Code', 'Valeur', 'Unité', 'Statut'];
        } elseif (isset($this->data['decisions'])) {
            return ['Titre', 'Statut', 'Impact', 'Date', 'Créé par'];
        } else {
            return ['Période', 'Chiffre d\'affaires', 'Marge', 'Taux de marge (%)'];
        }
    }
    
    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 12], 'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '1B6EF3']]],
        ];
    }
}