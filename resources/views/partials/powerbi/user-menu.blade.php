{{-- resources/views/partials/user-menu.blade.php --}}
<div class="dropdown">
    <button class="btn btn-light btn-sm dropdown-toggle d-flex align-items-center gap-2" 
            type="button" data-bs-toggle="dropdown">
        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" 
             style="width: 32px; height: 32px;">
            {{ substr(Auth::user()->nom, 0, 1) }}
        </div>
        <div class="text-start">
            <div class="fw-semibold small">{{ Auth::user()->nom }}</div>
            <div class="text-muted small">{{ Auth::user()->matricule }}</div>
        </div>
    </button>
    <ul class="dropdown-menu dropdown-menu-end shadow-sm">
        <li><a class="dropdown-item" href="#"><i class="fas fa-user-circle"></i> Mon profil</a></li>
        <li><a class="dropdown-item" href="{{ route('password.change') }}"><i class="fas fa-key"></i> Changer mot de passe</a></li>
        <li><hr class="dropdown-divider"></li>
        <li>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="dropdown-item text-danger">
                    <i class="fas fa-sign-out-alt"></i> Déconnexion
                </button>
            </form>
        </li>
    </ul>
</div>