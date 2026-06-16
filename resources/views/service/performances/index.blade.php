{{-- resources/views/service/performances/index.blade.php --}}
@extends('layouts.powerbi')

@section('title', 'Performances')
@section('page-title', 'Performances du service')
@section('breadcrumb', 'Service / Performances')

@section('content')
<div class="container-fluid px-0">
    
    {{-- KPIs --}}
    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="powerbi-card text-center">
                <h5>Chiffre d'affaires</h5>
                <div class="display-5 text-primary">{{ number_format($caTotal, 0, ',', ' ') }} {{ $devise }}</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="powerbi-card text-center">
                <h5>Marge</h5>
                <div class="display-5 text-success">{{ number_format($margeTotal, 0, ',', ' ') }} {{ $devise }}</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="powerbi-card text-center">
                <h5>Dépenses</h5>
                <div class="display-5 text-danger">{{ number_format($depensesTotal, 0, ',', ' ') }} {{ $devise }}</div>
            </div>
        </div>
    </div>

    {{-- Objectifs --}}
    <div class="row g-4 mb-4">
        <div class="col-12">
            <div class="powerbi-card">
                <h5 class="mb-3">Objectifs</h5>
                <div class="mb-3">
                    <div class="d-flex justify-content-between">
                        <span>Objectif CA</span>
                        <span>{{ number_format($objectifs['ca']['actuel'], 0, ',', ' ') }} / {{ number_format($objectifs['ca']['objectif'], 0, ',', ' ') }} {{ $devise }}</span>
                    </div>
                    <div class="progress" style="height: 10px;">
                        <div class="progress-bar bg-primary" style="width: {{ min($objectifs['ca']['progression'], 100) }}%"></div>
                    </div>
                </div>
                <div class="mb-3">
                    <div class="d-flex justify-content-between">
                        <span>Objectif Marge</span>
                        <span>{{ number_format($objectifs['marge']['actuel'], 0, ',', ' ') }} / {{ number_format($objectifs['marge']['objectif'], 0, ',', ' ') }} {{ $devise }}</span>
                    </div>
                    <div class="progress" style="height: 10px;">
                        <div class="progress-bar bg-success" style="width: {{ min($objectifs['marge']['progression'], 100) }}%"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection