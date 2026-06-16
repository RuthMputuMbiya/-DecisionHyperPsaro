{{-- resources/views/partials/powerbi/loading-spinner.blade.php --}}
@props([
    'size' => 'md', // sm, md, lg
    'text' => 'Chargement...',
    'fullscreen' => false
])

@php
    $sizes = [
        'sm' => ['spinner' => 24, 'icon' => 16],
        'md' => ['spinner' => 40, 'icon' => 24],
        'lg' => ['spinner' => 60, 'icon' => 32]
    ];
    $dimensions = $sizes[$size] ?? $sizes['md'];
@endphp

@if($fullscreen)
    <div class="loading-fullscreen" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(255,255,255,0.9); z-index: 9999; display: flex; align-items: center; justify-content: center; flex-direction: column;">
        <div class="loading-spinner" style="width: {{ $dimensions['spinner'] }}px; height: {{ $dimensions['spinner'] }}px; border: 3px solid #e2e8f0; border-top-color: #1a56db; border-radius: 50%; animation: spin 0.8s linear infinite;"></div>
        @if($text)
            <p style="margin-top: 20px; color: #1a56db; font-weight: 500;">{{ $text }}</p>
        @endif
    </div>
@else
    <div class="loading-container" style="display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 40px;">
        <div class="loading-spinner" style="width: {{ $dimensions['spinner'] }}px; height: {{ $dimensions['spinner'] }}px; border: 3px solid #e2e8f0; border-top-color: #1a56db; border-radius: 50%; animation: spin 0.8s linear infinite;"></div>
        @if($text)
            <p class="loading-text" style="margin-top: 15px; color: #6c757d; font-size: 14px;">{{ $text }}</p>
        @endif
    </div>
@endif

<style>
    @keyframes spin {
        to { transform: rotate(360deg); }
    }
</style>