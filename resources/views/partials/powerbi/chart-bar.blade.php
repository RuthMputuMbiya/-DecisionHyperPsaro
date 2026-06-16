{{-- resources/views/partials/powerbi/breadcrumb.blade.php --}}
<nav aria-label="breadcrumb">
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item">
            <a href="{{ route('dashboard') }}" class="text-decoration-none">
                <i class="fas fa-home"></i> Accueil
            </a>
        </li>
        @foreach($breadcrumbs ?? [] as $crumb)
            @if(!$loop->last)
                <li class="breadcrumb-item">
                    <a href="{{ $crumb['url'] }}" class="text-decoration-none">
                        {{ $crumb['label'] }}
                    </a>
                </li>
            @else
                <li class="breadcrumb-item active" aria-current="page">
                    {{ $crumb['label'] }}
                </li>
            @endif
        @endforeach
    </ol>
</nav>