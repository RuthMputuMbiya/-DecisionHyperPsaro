{{-- resources/views/partials/powerbi/empty-state.blade.php --}}
@props([
    'icon' => 'fas fa-chart-line',
    'title' => 'Aucune donnée',
    'message' => 'Aucune donnée disponible pour le moment',
    'actionText' => null,
    'actionLink' => null
])

<div class="empty-state" style="text-align: center; padding: 60px 20px;">
    <div class="empty-icon" style="width: 80px; height: 80px; background: #f8fafc; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px;">
        <i class="{{ $icon }}" style="font-size: 40px; color: #cbd5e1;"></i>
    </div>
    <h3 style="font-size: 18px; font-weight: 600; color: #1e293b; margin-bottom: 10px;">{{ $title }}</h3>
    <p style="color: #64748b; margin-bottom: 20px;">{{ $message }}</p>
    @if($actionText && $actionLink)
        <a href="{{ $actionLink }}" class="btn-empty-action" style="display: inline-flex; align-items: center; gap: 8px; padding: 10px 20px; background: linear-gradient(135deg, #1a56db, #7e3af2); color: white; border-radius: 12px; text-decoration: none; font-weight: 500;">
            <i class="fas fa-plus"></i> {{ $actionText }}
        </a>
    @endif
</div>