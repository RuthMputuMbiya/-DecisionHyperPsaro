{{-- resources/views/auth/change-password.blade.php --}}
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>HYPER PSARO - Changer mot de passe</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: linear-gradient(135deg, #0F2027 0%, #203A43 50%, #2C5364 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            position: relative;
        }

        .password-card {
            background: white;
            border-radius: 24px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.2);
            overflow: hidden;
            width: 100%;
            max-width: 500px;
            margin: 20px;
            animation: fadeIn 0.5s ease-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .password-header {
            background: linear-gradient(135deg, #1a56db, #7e3af2);
            padding: 30px;
            text-align: center;
            color: white;
        }

        .logo-icon {
            width: 60px;
            height: 60px;
            background: rgba(255,255,255,0.2);
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
            font-size: 28px;
        }

        .password-header h2 {
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 5px;
        }

        .password-header p {
            opacity: 0.8;
            font-size: 13px;
        }

        .password-body {
            padding: 35px;
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-size: 14px;
            font-weight: 600;
            color: #1e293b;
        }

        .form-group label i {
            margin-right: 8px;
            color: #1a56db;
        }

        /* 🔥 INPUT CONTAINER MODERNE */
        .input-container {
            position: relative;
            width: 100%;
        }

        .input-container i.input-icon {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            font-size: 16px;
            z-index: 1;
            transition: all 0.3s;
        }

        .input-container input {
            width: 100%;
            padding: 12px 45px 12px 45px;
            border: 1.5px solid #e2e8f0;
            border-radius: 12px;
            font-size: 14px;
            transition: all 0.3s;
            font-family: 'Segoe UI', sans-serif;
        }

        .input-container input:focus {
            outline: none;
            border-color: #1a56db;
            box-shadow: 0 0 0 3px rgba(26,86,219,0.1);
        }

        .input-container input:focus + .input-icon {
            color: #1a56db;
        }

        /* 🔥 BOUTON ŒIL À L'INTÉRIEUR */
        .password-toggle {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #94a3b8;
            cursor: pointer;
            font-size: 18px;
            padding: 0;
            z-index: 1;
            transition: color 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .password-toggle:hover {
            color: #1a56db;
        }

        .btn-change {
            width: 100%;
            background: linear-gradient(135deg, #1a56db, #7e3af2);
            color: white;
            border: none;
            padding: 14px;
            border-radius: 12px;
            font-weight: 700;
            font-size: 16px;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .btn-change:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(26,86,219,0.3);
        }

        .alert {
            padding: 12px 16px;
            border-radius: 12px;
            margin-bottom: 20px;
            font-size: 13px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .alert-danger {
            background: #FEF2F2;
            border: 1px solid #FECACA;
            color: #DC2626;
        }

        .alert-success {
            background: #ECFDF5;
            border: 1px solid #A7F3D0;
            color: #059669;
        }

        .alert-warning {
            background: #FEF3C7;
            border: 1px solid #FDE68A;
            color: #D97706;
        }

        .info-message {
            background: #EFF6FF;
            border-radius: 12px;
            padding: 15px;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 12px;
            border-left: 4px solid #1a56db;
        }

        .info-message i {
            font-size: 24px;
            color: #1a56db;
        }

        .info-message p {
            margin: 0;
            font-size: 13px;
            color: #1e293b;
        }

        .requirements {
            margin-top: 15px;
            padding: 12px;
            background: #f8fafc;
            border-radius: 10px;
        }

        .requirements p {
            font-size: 12px;
            margin-bottom: 8px;
            color: #64748b;
        }

        .requirements ul {
            margin: 0;
            padding-left: 20px;
            font-size: 11px;
            color: #64748b;
        }

        .requirements li {
            margin: 5px 0;
        }
    </style>
</head>
<body>
    <div class="password-card">
        <div class="password-header">
            <div class="logo-icon">
                <i class="fas fa-key"></i>
            </div>
            <h2>Changement de mot de passe</h2>
            <p>Bienvenue {{ Auth::user()->nom }} !</p>
        </div>
        
        <div class="password-body">
            
            <div class="info-message">
                <i class="fas fa-info-circle"></i>
                <p>
                    <strong>Première connexion !</strong><br>
                    Veuillez changer votre mot de passe par défaut pour continuer.
                </p>
            </div>

            @if(session('error'))
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle"></i>
                    {{ session('error') }}
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle"></i>
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('password.change.submit') }}" id="passwordForm">
                @csrf

                <!-- Champ Mot de passe actuel -->
                <div class="form-group">
                    <label><i class="fas fa-lock"></i> Mot de passe actuel</label>
                    <div class="input-container">
                        <i class="fas fa-key input-icon"></i>
                        <input type="password" name="current_password" id="current_password" 
                               placeholder="Votre mot de passe actuel" required>
                        <button type="button" class="password-toggle" onclick="togglePassword('current_password')">
                            <i class="far fa-eye"></i>
                        </button>
                    </div>
                    <div class="form-text text-muted small mt-1">
                        <i class="fas fa-info-circle"></i> Mot de passe par défaut : <strong>password123</strong>
                    </div>
                </div>

                <!-- Champ Nouveau mot de passe -->
                <div class="form-group">
                    <label><i class="fas fa-lock"></i> Nouveau mot de passe</label>
                    <div class="input-container">
                        <i class="fas fa-key input-icon"></i>
                        <input type="password" name="new_password" id="new_password" 
                               placeholder="Nouveau mot de passe" required>
                        <button type="button" class="password-toggle" onclick="togglePassword('new_password')">
                            <i class="far fa-eye"></i>
                        </button>
                    </div>
                </div>

                <!-- Champ Confirmer le mot de passe -->
                <div class="form-group">
                    <label><i class="fas fa-lock"></i> Confirmer le mot de passe</label>
                    <div class="input-container">
                        <i class="fas fa-key input-icon"></i>
                        <input type="password" name="new_password_confirmation" id="confirm_password" 
                               placeholder="Confirmer le nouveau mot de passe" required>
                        <button type="button" class="password-toggle" onclick="togglePassword('confirm_password')">
                            <i class="far fa-eye"></i>
                        </button>
                    </div>
                </div>

                <div class="requirements">
                    <p><i class="fas fa-shield-alt"></i> <strong>Exigences du mot de passe :</strong></p>
                    <ul>
                        <li>Minimum 8 caractères</li>
                        <li>Au moins une majuscule</li>
                        <li>Au moins une minuscule</li>
                        <li>Au moins un chiffre</li>
                    </ul>
                </div>

                <button type="submit" class="btn-change">
                    <i class="fas fa-save"></i> Changer mon mot de passe
                </button>
            </form>
        </div>
    </div>

    <script>
        function togglePassword(fieldId) {
            const field = document.getElementById(fieldId);
            const type = field.getAttribute('type') === 'password' ? 'text' : 'password';
            field.setAttribute('type', type);
            
            // Changer l'icône (œil ouvert/fermé)
            const button = field.nextElementSibling;
            const icon = button.querySelector('i');
            if (icon) {
                icon.classList.toggle('fa-eye');
                icon.classList.toggle('fa-eye-slash');
            }
        }

        // Validation du formulaire
        document.getElementById('passwordForm').addEventListener('submit', function(e) {
            const newPassword = document.getElementById('new_password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            
            if (newPassword !== confirmPassword) {
                e.preventDefault();
                alert('Les mots de passe ne correspondent pas !');
                return false;
            }
            
            if (newPassword.length < 8) {
                e.preventDefault();
                alert('Le mot de passe doit contenir au moins 8 caractères !');
                return false;
            }
        });
    </script>
</body>
</html>