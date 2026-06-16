{{-- resources/views/auth/login.blade.php --}}
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>HYPER PSARO - Connexion | Système Décisionnel</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #3482a3 0%, #3f8098 50%, #2C5364 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow-x: hidden;
        }

        /* Background animation */
        body::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1000 1000"><circle cx="100" cy="100" r="80" fill="rgba(0,180,219,0.03)"/><circle cx="800" cy="200" r="120" fill="rgba(0,180,219,0.03)"/><circle cx="500" cy="800" r="150" fill="rgba(0,180,219,0.03)"/><circle cx="300" cy="500" r="100" fill="rgba(0,180,219,0.02)"/><circle cx="700" cy="600" r="90" fill="rgba(0,180,219,0.02)"/></svg>');
            background-size: cover;
            opacity: 0.5;
            pointer-events: none;
        }

        /* Floating shapes */
        .floating-shape {
            position: absolute;
            background: linear-gradient(135deg, rgba(0,180,219,0.1), rgba(0,131,176,0.05));
            border-radius: 50%;
            filter: blur(60px);
            pointer-events: none;
        }

        .shape-1 { width: 300px; height: 300px; top: -100px; left: -100px; }
        .shape-2 { width: 400px; height: 400px; bottom: -150px; right: -150px; }

        /* Login Card */
        .login-container {
            width: 100%;
            max-width: 450px;
            margin: 20px;
            position: relative;
            z-index: 10;
        }

        .login-card {
            background: rgba(255, 255, 255, 0.98);
            border-radius: 32px;
            overflow: hidden;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            backdrop-filter: blur(10px);
            transition: transform 0.3s ease;
        }

        .login-card:hover {
            transform: translateY(-5px);
        }

        /* Header */
        .login-header {
            background: linear-gradient(135deg, #0F2027, #203A43);
            padding: 30px;
            text-align: center;
            position: relative;
        }

        .back-btn {
            position: absolute;
            top: 20px;
            left: 20px;
            background: rgba(255,255,255,0.15);
            border: none;
            color: white;
            width: 36px;
            height: 36px;
            border-radius: 50%;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s;
            text-decoration: none;
        }

        .back-btn:hover {
            background: rgba(255,255,255,0.3);
            transform: translateX(-3px);
        }

        .logo-icon {
            width: 70px;
            height: 70px;
            background: linear-gradient(135deg, #00B4DB, #0083B0);
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
            font-size: 32px;
            color: white;
            box-shadow: 0 8px 20px rgba(0,180,219,0.3);
        }

        .login-header h2 {
            color: white;
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 5px;
        }

        .login-header p {
            color: rgba(255,255,255,0.7);
            font-size: 13px;
        }

        /* Body */
        .login-body {
            padding: 35px;
        }

        /* Form Group - Style moderne */
        .form-group {
            margin-bottom: 24px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-size: 14px;
            font-weight: 600;
            color: #1E293B;
        }

        .form-group label i {
            margin-right: 8px;
            color: #00B4DB;
        }

        /* Input container moderne */
        .input-container {
            position: relative;
            width: 100%;
        }

        .input-container i.input-icon {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: #94A3B8;
            font-size: 16px;
            z-index: 1;
            transition: all 0.3s;
        }

        .input-container input {
            width: 100%;
            padding: 14px 45px 14px 45px;
            border: 1.5px solid #E2E8F0;
            border-radius: 16px;
            font-size: 15px;
            transition: all 0.3s;
            font-family: 'Inter', sans-serif;
            background: white;
        }

        .input-container input:focus {
            outline: none;
            border-color: #00B4DB;
            box-shadow: 0 0 0 3px rgba(0,180,219,0.1);
        }

        .input-container input:focus + .input-icon {
            color: #00B4DB;
        }

        /* Password toggle à l'intérieur */
        .password-toggle {
            position: absolute;
            right: 16px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #94A3B8;
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
            color: #00B4DB;
        }

        /* Options */
        .form-options {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }

        .checkbox {
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
            font-size: 13px;
            color: #475569;
        }

        .checkbox input {
            width: 16px;
            height: 16px;
            cursor: pointer;
            accent-color: #00B4DB;
        }

        .forgot-link {
            font-size: 13px;
            color: #00B4DB;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s;
        }

        .forgot-link:hover {
            color: #0083B0;
        }

        /* Login Button */
        .btn-login {
            width: 100%;
            background: linear-gradient(135deg, #00B4DB, #0083B0);
            border: none;
            padding: 14px;
            border-radius: 16px;
            color: white;
            font-weight: 700;
            font-size: 16px;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            margin-bottom: 20px;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0,180,219,0.3);
        }

        /* Alert */
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

        /* Footer card */
        .login-footer {
            padding: 20px 35px 30px;
            text-align: center;
            border-top: 1px solid #E2E8F0;
            background: #F8FAFC;
        }

        .login-footer p {
            font-size: 12px;
            color: #64748B;
        }

        .login-footer a {
            color: #00B4DB;
            text-decoration: none;
        }

        /* Responsive */
        @media (max-width: 480px) {
            .login-body { padding: 25px; }
            .login-footer { padding: 20px 25px; }
        }

        /* Animation */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .login-card {
            animation: fadeIn 0.6s ease-out;
        }
    </style>
</head>
<body>
    <div class="floating-shape shape-1"></div>
    <div class="floating-shape shape-2"></div>

    <div class="login-container">
        <div class="login-card">
            <!-- Header avec bouton retour -->
            <div class="login-header">
                <a href="{{ url('/') }}" class="back-btn" title="Retour à l'accueil">
                    <i class="fas fa-arrow-left"></i>
                </a>
                <div class="logo-icon">
                    <i class="fas fa-chart-line"></i>
                </div>
                <h2>Connexion</h2>
                <p>Système Décisionnel Power </p>
            </div>

            <!-- Body -->
            <div class="login-body">
                @if(session('success'))
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i>
                        {{ session('success') }}
                    </div>
                @endif

                @if($errors->any())
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle"></i>
                        {{ $errors->first() }}
                    </div>
                @endif

                <form method="POST" action="{{ route('login') }}" id="loginForm">
                    @csrf

                    <!-- Champ Matricule ou Email -->
                    <div class="form-group">
                        <label><i class="fas fa-id-card"></i> Matricule ou Email</label>
                        <div class="input-container">
                            <i class="fas fa-envelope input-icon"></i>
                            <input type="text" name="identifiant" 
                                   placeholder="ex:perpetue@gmail.com" 
                                   value="{{ old('identifiant') }}" required autofocus>
                        </div>
                    </div>

                    <!-- Champ Mot de passe avec œil intégré -->
                    <div class="form-group">
                        <label><i class="fas fa-lock"></i> Mot de passe</label>
                        <div class="input-container">
                            <i class="fas fa-key input-icon"></i>
                            <input type="password" name="password" id="password" 
                                   placeholder="••••••••" required>
                            <button type="button" class="password-toggle" id="togglePassword">
                                <i class="far fa-eye"></i>
                            </button>
                        </div>
                    </div>

                    <div class="form-options">
                        <label class="checkbox">
                            <input type="checkbox" name="remember"> Se souvenir de moi
                        </label>
                        <a href="#" class="forgot-link">Mot de passe oublié ?</a>
                    </div>

                    <button type="submit" class="btn-login">
                        <i class="fas fa-sign-in-alt"></i> Se connecter
                    </button>
                </form>
            </div>

            <!-- Footer -->
            <div class="login-footer">
                <p>
                    <i class="fas fa-shield-alt"></i> Espace sécurisé - HYPER PSARO
                </p>
                <p style="margin-top: 8px;">
                    <a href="{{ url('/') }}"><i class="fas fa-home"></i> Retour à l'accueil</a>
                </p>
            </div>
        </div>
    </div>

    <script>
        // Toggle password visibility - œil à l'intérieur du champ
        const togglePassword = document.getElementById('togglePassword');
        const password = document.getElementById('password');

        togglePassword.addEventListener('click', function() {
            const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
            password.setAttribute('type', type);
            
            // Changer l'icône (œil ouvert/fermé)
            const icon = this.querySelector('i');
            icon.classList.toggle('fa-eye');
            icon.classList.toggle('fa-eye-slash');
        });

        // Form validation
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            const identifiant = document.querySelector('input[name="identifiant"]').value.trim();
            const password = document.querySelector('input[name="password"]').value;
            
            if (!identifiant || !password) {
                e.preventDefault();
                alert('Veuillez remplir tous les champs');
            }
        });
    </script>
</body>
</html>