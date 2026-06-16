<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>HYPER PSARO | Pilotage Décisionnel </title>
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
            background: #FFFFFF;
            overflow-x: hidden;
        }

        /* Navigation - Bleu électrique */
        .navbar {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            background: linear-gradient(135deg, #0F2027, #203A43);
            backdrop-filter: blur(10px);
            z-index: 1000;
            padding: 16px 0;
            transition: all 0.3s;
            box-shadow: 0 4px 20px rgba(0,0,0,0.2);
        }

        .navbar.scrolled {
            padding: 12px 0;
            background: linear-gradient(135deg, #0F2027, #203A43);
        }

        .nav-container {
            max-width: 1280px;
            margin: 0 auto;
            padding: 0 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
        }

        .logo-icon {
            width: 44px;
            height: 44px;
            background: linear-gradient(135deg, #00B4DB, #0083B0);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            font-size: 20px;
            color: white;
            box-shadow: 0 4px 15px rgba(0,180,219,0.3);
        }

        .logo-text {
            font-size: 22px;
            font-weight: 800;
            color: white;
            letter-spacing: -0.3px;
        }

        .logo-text span {
            color: #00B4DB;
        }

        .logo-sub {
            font-size: 10px;
            color: #B8C4DC;
            letter-spacing: 0.4px;
        }

        .nav-links {
            display: flex;
            gap: 32px;
            align-items: center;
        }

        .nav-link {
            text-decoration: none;
            color: rgba(255,255,255,0.8);
            font-weight: 500;
            transition: color 0.3s;
            font-size: 15px;
        }

        .nav-link:hover {
            color: #00B4DB;
        }

        .btn-login {
            background: linear-gradient(135deg, #00B4DB, #0083B0);
            color: white;
            padding: 10px 28px;
            border-radius: 40px;
            text-decoration: none;
            font-weight: 700;
            transition: all 0.3s;
            box-shadow: 0 4px 15px rgba(0,180,219,0.3);
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0,180,219,0.4);
        }

        .mobile-menu-btn {
            display: none;
            background: none;
            border: none;
            font-size: 24px;
            cursor: pointer;
            color: white;
        }

        /* Hero Section - Dégradé bleu/violet */
        .hero {
            min-height: 100vh;
            display: flex;
            align-items: center;
            padding: 120px 24px 80px;
            background: linear-gradient(135deg, #0F2027 0%, #203A43 50%, #2C5364 100%);
            position: relative;
            overflow: hidden;
        }

        .hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1000 1000"><circle cx="100" cy="100" r="80" fill="rgba(0,180,219,0.05)"/><circle cx="800" cy="200" r="120" fill="rgba(0,180,219,0.05)"/><circle cx="500" cy="800" r="150" fill="rgba(0,180,219,0.05)"/></svg>');
            background-size: cover;
            opacity: 0.5;
        }

        .hero-container {
            max-width: 1280px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 60px;
            align-items: center;
            position: relative;
            z-index: 1;
        }

        .hero-badge {
            display: inline-block;
            background: rgba(0,180,219,0.15);
            padding: 6px 16px;
            border-radius: 50px;
            font-size: 13px;
            color: #00B4DB;
            margin-bottom: 24px;
            font-weight: 500;
            border: 1px solid rgba(0,180,219,0.3);
        }

        .hero-title {
            font-size: 52px;
            font-weight: 800;
            color: white;
            line-height: 1.2;
            margin-bottom: 24px;
        }

        .hero-title span {
            background: linear-gradient(135deg, #00B4DB, #667eea);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }

        .hero-description {
            font-size: 17px;
            color: rgba(255,255,255,0.8);
            margin-bottom: 36px;
            line-height: 1.6;
        }

        .hero-buttons {
            display: flex;
            gap: 20px;
            margin-bottom: 50px;
            flex-wrap: wrap;
        }

        .btn-hero-primary {
            background: linear-gradient(135deg, #00B4DB, #0083B0);
            color: white;
            padding: 14px 38px;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 700;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            box-shadow: 0 4px 15px rgba(0,180,219,0.3);
        }

        .btn-hero-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0,180,219,0.4);
        }

        .btn-hero-secondary {
            background: rgba(255,255,255,0.1);
            color: white;
            padding: 14px 38px;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
            border: 1px solid rgba(255,255,255,0.2);
            display: inline-flex;
            align-items: center;
            gap: 10px;
        }

        .btn-hero-secondary:hover {
            border-color: #00B4DB;
            color: #00B4DB;
            transform: translateY(-3px);
            background: rgba(0,180,219,0.1);
        }

        .hero-stats {
            display: flex;
            gap: 48px;
            padding-top: 24px;
            border-top: 1px solid rgba(0,180,219,0.3);
        }

        .hero-stats .stat h3 {
            font-size: 32px;
            font-weight: 800;
            background: linear-gradient(135deg, #00B4DB, #667eea);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }

        .hero-stats .stat p {
            font-size: 13px;
            color: rgba(255,255,255,0.7);
            font-weight: 500;
        }

        .hero-image {
            animation: float 6s ease-in-out infinite;
            text-align: center;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-20px); }
        }

        /* Features Section - Blanc */
        .features {
            background: #FFFFFF;
            padding: 80px 24px;
        }

        .section-container {
            max-width: 1280px;
            margin: 0 auto;
        }

        .section-header {
            text-align: center;
            margin-bottom: 60px;
        }

        .section-title {
            font-size: 38px;
            font-weight: 800;
            background: linear-gradient(135deg, #0F2027, #00B4DB);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            margin-bottom: 16px;
        }

        .section-title span {
            color: #00B4DB;
        }

        .section-subtitle {
            color: #64748B;
            font-size: 18px;
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(310px, 1fr));
            gap: 32px;
        }

        .feature-card {
            background: #FFFFFF;
            border-radius: 24px;
            padding: 40px 28px;
            text-align: center;
            transition: all 0.35s ease;
            border: 1px solid #E2E8F0;
            box-shadow: 0 4px 12px rgba(0,0,0,0.02);
        }

        .feature-card:hover {
            transform: translateY(-10px);
            border-color: #00B4DB;
            box-shadow: 0 20px 35px -12px rgba(0,180,219,0.2);
        }

        .feature-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, rgba(0,180,219,0.1), rgba(102,126,234,0.1));
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 28px;
            font-size: 36px;
            color: #00B4DB;
        }

        .feature-title {
            font-size: 22px;
            font-weight: 700;
            margin-bottom: 14px;
            color: #0F172A;
        }

        .feature-description {
            color: #475569;
            line-height: 1.55;
            font-size: 14px;
        }

        /* CTA Section - Blanc */
        .cta {
            padding: 80px 24px;
            background: #FFFFFF;
            text-align: center;
            border-top: 1px solid #E2E8F0;
        }

        .cta-title {
            font-size: 36px;
            font-weight: 800;
            background: linear-gradient(135deg, #0F2027, #00B4DB);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            margin-bottom: 20px;
        }

        .cta-description {
            font-size: 18px;
            color: #475569;
            margin-bottom: 40px;
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
        }

        .cta-buttons {
            display: flex;
            gap: 24px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .btn-cta-primary {
            background: linear-gradient(135deg, #00B4DB, #0083B0);
            color: white;
            padding: 14px 40px;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 700;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            font-size: 16px;
            box-shadow: 0 4px 15px rgba(0,180,219,0.3);
        }

        .btn-cta-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(0,180,219,0.4);
        }

        .btn-cta-secondary {
            background: white;
            border: 1.5px solid #00B4DB;
            color: #00B4DB;
            padding: 14px 40px;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 10px;
        }

        .btn-cta-secondary:hover {
            background: linear-gradient(135deg, #00B4DB, #0083B0);
            color: white;
            transform: translateY(-3px);
        }

        /* Footer - NOIR */
        .footer {
            background: #0A0A0F;
            color: #94A3B8;
            padding: 60px 24px 30px;
            border-top: 1px solid rgba(0,180,219,0.2);
        }

        .footer-container {
            max-width: 1280px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 45px;
        }

        .footer-logo {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 18px;
        }

        .footer-logo-icon {
            width: 38px;
            height: 38px;
            background: linear-gradient(135deg, #00B4DB, #0083B0);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            font-size: 16px;
            color: white;
        }

        .footer-logo-text {
            font-weight: 700;
            font-size: 20px;
            color: white;
        }

        .footer-logo-text span {
            color: #00B4DB;
        }

        .footer h4 {
            color: white;
            margin-bottom: 22px;
            font-weight: 600;
            font-size: 16px;
        }

        .footer-links {
            list-style: none;
        }

        .footer-links li {
            margin-bottom: 12px;
        }

        .footer-links a {
            color: #94A3B8;
            text-decoration: none;
            transition: color 0.3s;
            font-size: 14px;
        }

        .footer-links a:hover {
            color: #00B4DB;
        }

        .footer-bottom {
            max-width: 1280px;
            margin: 45px auto 0;
            padding-top: 28px;
            text-align: center;
            border-top: 1px solid #1E293B;
            font-size: 13px;
            color: #64748B;
        }

        @media (max-width: 768px) {
            .mobile-menu-btn { display: block; }
            .nav-links {
                display: none;
                position: absolute;
                top: 100%;
                left: 0;
                right: 0;
                background: linear-gradient(135deg, #0F2027, #203A43);
                flex-direction: column;
                padding: 20px;
                gap: 20px;
            }
            .nav-links.show { display: flex; }
            .hero-container { grid-template-columns: 1fr; text-align: center; }
            .hero-title { font-size: 36px; }
            .hero-buttons { justify-content: center; }
            .hero-stats { justify-content: center; flex-wrap: wrap; gap: 30px; }
            .features-grid { grid-template-columns: 1fr; }
            .footer-container { grid-template-columns: 1fr; text-align: center; }
            .footer-logo { justify-content: center; }
        }

        .reveal {
            opacity: 0;
            transform: translateY(30px);
            transition: all 0.8s ease;
        }

        .reveal.active {
            opacity: 1;
            transform: translateY(0);
        }
    </style>
</head>
<body>
    <nav class="navbar" id="navbar">
        <div class="nav-container">
            <a href="{{ url('/') }}" class="logo">
                <div class="logo-icon">
                    <i class="fas fa-chart-line"></i>
                </div>
                <div>
                    <div class="logo-text">HYPER <span>PSARO</span></div>
                    <div class="logo-sub">SYSTÈME DÉCISIONNEL</div>
                </div>
            </a>
            <button class="mobile-menu-btn" id="mobileMenuBtn">☰</button>
            <div class="nav-links" id="navLinks">
                <a href="#fonctionnalites" class="nav-link">Fonctionnalités</a>
                <a href="#contact" class="nav-link">Contact</a>
                <a href="{{ route('login') }}" class="btn-login">
                    <i class="fas fa-sign-in-alt"></i> Connexion
                </a>
            </div>
        </div>
    </nav>

    <!-- Hero Section - Haut en dégradé bleu -->
    <section class="hero">
        <div class="hero-container">
            <div class="hero-content">
                <div class="hero-badge">✦ DASHBOARD ✦</div>
                <h1 class="hero-title">
                    Pilotage financier<br>
                    <span>haute performance</span>
                </h1>
                <p class="hero-description">
                    HYPER PSARO vous offre une vision 360° de vos indicateurs financiers. 
                    Tableaux de bord interactifs style , simulations avancées et analyse multi-devises en temps réel.
                </p>
                <div class="hero-buttons">
                    <a href="{{ route('login') }}" class="btn-hero-primary">
                        <i class="fas fa-chart-simple"></i> Accéder au Dashboard
                    </a>
                    <a href="#fonctionnalites" class="btn-hero-secondary">
                        <i class="fas fa-chevron-down"></i> Découvrir
                    </a>
                </div>
                <div class="hero-stats">
                    
                    <div class="stat"><h3>3</h3><p>DEVISES</p></div>
                    <div class="stat"><h3>24/7</h3><p>DISPONIBLE</p></div>
                    <div class="stat"><h3>100%</h3><p>SÉCURISÉ</p></div>
                </div>
            </div>
            <div class="hero-image">
                <svg width="400" height="400" viewBox="0 0 500 500" xmlns="http://www.w3.org/2000/svg">
                    <rect x="50" y="100" width="400" height="300" rx="20" fill="rgba(0,180,219,0.05)" stroke="#00B4DB" stroke-width="1.5"/>
                    <rect x="70" y="130" width="360" height="40" rx="8" fill="rgba(0,180,219,0.1)" />
                    <rect x="70" y="190" width="360" height="200" rx="8" fill="rgba(255,255,255,0.03)" />
                    <rect x="100" y="340" width="40" height="50" rx="5" fill="#00B4DB">
                        <animate attributeName="height" values="50;80;50" dur="2s" repeatCount="indefinite"/>
                        <animate attributeName="y" values="340;310;340" dur="2s" repeatCount="indefinite"/>
                    </rect>
                    <rect x="160" y="310" width="40" height="80" rx="5" fill="#0083B0">
                        <animate attributeName="height" values="80;60;80" dur="2.5s" repeatCount="indefinite"/>
                        <animate attributeName="y" values="310;330;310" dur="2.5s" repeatCount="indefinite"/>
                    </rect>
                    <rect x="220" y="280" width="40" height="110" rx="5" fill="#00B4DB">
                        <animate attributeName="height" values="110;90;110" dur="1.8s" repeatCount="indefinite"/>
                        <animate attributeName="y" values="280;300;280" dur="1.8s" repeatCount="indefinite"/>
                    </rect>
                    <rect x="280" y="300" width="40" height="90" rx="5" fill="#0083B0">
                        <animate attributeName="height" values="90;110;90" dur="2.2s" repeatCount="indefinite"/>
                        <animate attributeName="y" values="300;280;300" dur="2.2s" repeatCount="indefinite"/>
                    </rect>
                    <rect x="340" y="330" width="40" height="60" rx="5" fill="#00B4DB">
                        <animate attributeName="height" values="60;40;60" dur="1.5s" repeatCount="indefinite"/>
                        <animate attributeName="y" values="330;350;330" dur="1.5s" repeatCount="indefinite"/>
                    </rect>
                    <polyline points="100,340 150,330 200,350 250,310 300,320 350,300" fill="none" stroke="#10B981" stroke-width="3"/>
                    <circle cx="100" cy="340" r="4" fill="#10B981"/>
                    <circle cx="150" cy="330" r="4" fill="#10B981"/>
                    <circle cx="200" cy="350" r="4" fill="#10B981"/>
                    <circle cx="250" cy="310" r="4" fill="#10B981"/>
                    <circle cx="300" cy="320" r="4" fill="#10B981"/>
                    <circle cx="350" cy="300" r="4" fill="#10B981"/>
                </svg>
            </div>
        </div>
    </section>

    <!-- Features Section - Blanc -->
    <section class="features" id="fonctionnalites">
        <div class="section-container">
            <div class="section-header reveal">
                <h2 class="section-title">Fonctionnalités <span>DECISIONNEL</span></h2>
                <p class="section-subtitle">L'ensemble des outils pour un pilotage d'exception</p>
            </div>
            <div class="features-grid">
                <div class="feature-card reveal">
                    <div class="feature-icon"><i class="fas fa-chart-pie"></i></div>
                    <h3 class="feature-title">Tableaux de bord interactifs</h3>
                    <p class="feature-description">Visualisez vos KPI en temps réel avec des graphiques dynamiques style.</p>
                </div>
                <div class="feature-card reveal">
                    <div class="feature-icon"><i class="fas fa-chart-line"></i></div>
                    <h3 class="feature-title">Simulations financières</h3>
                    <p class="feature-description">Testez des scénarios et mesurez l'impact avant chaque décision stratégique.</p>
                </div>
                <div class="feature-card reveal">
                    <div class="feature-icon"><i class="fas fa-chart-simple"></i></div>
                    <h3 class="feature-title">Comparaisons avancées</h3>
                    <p class="feature-description">Comparez périodes, services et indicateurs avec écarts en valeur et pourcentage.</p>
                </div>
                <div class="feature-card reveal">
                    <div class="feature-icon"><i class="fas fa-file-pdf"></i></div>
                    <h3 class="feature-title">Rapports PDF</h3>
                    <p class="feature-description">Générez automatiquement des rapports complets pour vos comités de direction.</p>
                </div>
                <div class="feature-card reveal">
                    <div class="feature-icon"><i class="fas fa-shield-haltered"></i></div>
                    <h3 class="feature-title">Sécurité renforcée</h3>
                    <p class="feature-description">Authentification sécurisée et gestion granulaire des droits par profil utilisateur.</p>
                </div>
                <div class="feature-card reveal">
                    <div class="feature-icon"><i class="fas fa-history"></i></div>
                    <h3 class="feature-title">Historique des décisions</h3>
                    <p class="feature-description">Tracez toutes vos décisions et simulations pour un suivi complet.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta" id="contact">
        <div class="section-container">
            <h2 class="cta-title reveal">Prêt à transformer votre pilotage financier ?</h2>
            <p class="cta-description reveal">Accédez à votre tableau de bord et découvrez la puissance de HYPER PSARO.</p>
            <div class="cta-buttons reveal">
                <a href="{{ route('login') }}" class="btn-cta-primary">
                    <i class="fas fa-arrow-right-to-bracket"></i> Se connecter
                </a>
                <a href="#fonctionnalites" class="btn-cta-secondary">
                    <i class="fas fa-chevron-down"></i> En savoir plus
                </a>
            </div>
        </div>
    </section>

    <!-- Footer - NOIR -->
    <footer class="footer">
        <div class="footer-container">
            <div>
                <div class="footer-logo">
                    <div class="footer-logo-icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <div class="footer-logo-text">HYPER <span>PSARO</span></div>
                </div>
                <p style="color:#94A3B8; font-size:14px;">Système décisionnel pour le pilotage des performances financières.</p>
                <p style="margin-top: 15px; font-size: 12px; color:#00B4DB;">CDF • USD • EUR</p>
            </div>
            <div>
                <h4>Fonctionnalités</h4>
                <ul class="footer-links">
                    <li><a href="#">Dashboard </a></li>
                    <li><a href="#">Simulations</a></li>
                    <li><a href="#">Comparaisons</a></li>
                    <li><a href="#">Rapports PDF</a></li>
                </ul>
            </div>
          
            <div>
                <h4>Contact</h4>
                <ul class="footer-links">
                    <li><a href="#">support@hyperpsaro.com</a></li>
                    <li><a href="#">+243 812 345 678</a></li>
                    <li><a href="#">Lubumbashi, RDC</a></li>
                </ul>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; {{ date('Y') }} HYPER PSARO - Système Décisionnel . Tous droits réservés.</p>
        </div>
    </footer>

    <script>
        window.addEventListener('scroll', function() {
            const navbar = document.getElementById('navbar');
            navbar.classList.toggle('scrolled', window.scrollY > 50);
        });
        
        document.getElementById('mobileMenuBtn').addEventListener('click', function() {
            document.getElementById('navLinks').classList.toggle('show');
        });
        
        const reveals = document.querySelectorAll('.reveal');
        function reveal() {
            reveals.forEach(el => {
                if (el.getBoundingClientRect().top < window.innerHeight - 150) {
                    el.classList.add('active');
                }
            });
        }
        window.addEventListener('scroll', reveal);
        reveal();
    </script>
</body>
</html>