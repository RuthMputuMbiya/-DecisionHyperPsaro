{{-- resources/views/partials/powerbi/breadcrumb.blade.php --}}
@props([
    'items' => [],
    'home' => true,
    'separator' => '<i class="fas fa-chevron-right"></i>',
    'homeIcon' => '<i class="fas fa-home"></i>'
])

<nav aria-label="breadcrumb" style="margin-bottom: 20px;">
    <ol class="powerbi-breadcrumb" style="display: flex; flex-wrap: wrap; align-items: center; gap: 8px; list-style: none; padding: 0; margin: 0;">
        @if($home)
            <li class="breadcrumb-item" style="display: inline-flex; align-items: center;">
                <a href="{{ url('/') }}" style="color: #1a56db; text-decoration: none; font-size: 13px; transition: color 0.2s;">
                    {!! $homeIcon !!}
                    <span style="margin-left: 5px;">Accueil</span>
                </a>
                <span style="margin-left: 8px; color: #cbd5e1;">{!! $separator !!}</span>
            </li>
        @endif
        
        @foreach($items as $key => $item)
            <li class="breadcrumb-item" style="display: inline-flex; align-items: center;">
                @if($loop->last)
                    <span class="breadcrumb-active" style="color: #1e293b; font-weight: 600; font-size: 13px;">
                        @if(isset($item['icon']))
                            <i class="{{ $item['icon'] }}" style="margin-right: 6px; font-size: 12px;"></i>
                        @endif
                        {{ $item['label'] }}
                    </span>
                @else
                    <a href="{{ $item['url'] ?? '#' }}" class="breadcrumb-link" style="color: #6c757d; text-decoration: none; font-size: 13px; transition: color 0.2s;">
                        @if(isset($item['icon']))
                            <i class="{{ $item['icon'] }}" style="margin-right: 6px; font-size: 12px;"></i>
                        @endif
                        {{ $item['label'] }}
                    </a>
                    <span style="margin-left: 8px; color: #cbd5e1;">{!! $separator !!}</span>
                @endif
            </li>
        @endforeach
    </ol>
</nav>

<style>
    .breadcrumb-link:hover {
        color: #1a56db !important;
    }
    .breadcrumb-item {
        transition: all 0.2s;
    }
</style>