{{-- resources/views/partials/powerbi/footer.blade.php --}}
<footer class="powerbi-footer" style="background: #0A0A0F; color: #94A3B8; margin-top: 40px; padding: 40px 0 20px; border-top: 1px solid rgba(0,180,219,0.2);">
    <div class="container">
        <div class="row">
            {{-- Logo et description --}}
            <div class="col-md-4 mb-4 mb-md-0">
                <div class="footer-logo" style="display: flex; align-items: center; gap: 12px; margin-bottom: 15px;">
                    <div class="footer-logo-icon" style="width: 40px; height: 40px; background: linear-gradient(135deg, #00B4DB, #0083B0); border-radius: 10px; display: flex; align-items: center; justify-content: center; color: white;">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <div>
                        <div class="footer-logo-text" style="font-size: 20px; font-weight: 700; color: white;">HYPER <span style="color: #00B4DB;">PSARO</span></div>
                        <div style="font-size: 10px; color: #64748B;">Système Décisionnel Power BI</div>
                    </div>
                </div>
                <p style="font-size: 13px; line-height: 1.6; margin-bottom: 15px;">
                    Solution complète de pilotage financier avec tableaux de bord interactifs, simulations avancées et analyse multi-devises.
                </p>
                <div class="footer-social" style="display: flex; gap: 15px;">
                    <a href="#" style="color: #64748B; transition: color 0.3s;"><i class="fab fa-linkedin-in"></i></a>
                    <a href="#" style="color: #64748B; transition: color 0.3s;"><i class="fab fa-twitter"></i></a>
                    <a href="#" style="color: #64748B; transition: color 0.3s;"><i class="fab fa-facebook-f"></i></a>
                </div>
            </div>
            
            {{-- Liens rapides --}}
            <div class="col-md-2 col-6 mb-4 mb-md-0">
                <h4 style="color: white; font-size: 14px; font-weight: 600; margin-bottom: 20px;">Fonctionnalités</h4>
                <ul class="footer-links" style="list-style: none; padding: 0;">
                    <li style="margin-bottom: 10px;"><a href="#" style="color: #94A3B8; text-decoration: none; font-size: 13px; transition: color 0.3s;"><i class="fas fa-chart-line" style="font-size: 10px; margin-right: 8px;"></i> Dashboard</a></li>
                    <li style="margin-bottom: 10px;"><a href="#" style="color: #94A3B8; text-decoration: none; font-size: 13px; transition: color 0.3s;"><i class="fas fa-chart-pie" style="font-size: 10px; margin-right: 8px;"></i> KPIs</a></li>
                    <li style="margin-bottom: 10px;"><a href="#" style="color: #94A3B8; text-decoration: none; font-size: 13px; transition: color 0.3s;"><i class="fas fa-flask" style="font-size: 10px; margin-right: 8px;"></i> Simulations</a></li>
                    <li style="margin-bottom: 10px;"><a href="#" style="color: #94A3B8; text-decoration: none; font-size: 13px; transition: color 0.3s;"><i class="fas fa-file-pdf" style="font-size: 10px; margin-right: 8px;"></i> Rapports PDF</a></li>
                </ul>
            </div>
            
            <div class="col-md-2 col-6 mb-4 mb-md-0">
                <h4 style="color: white; font-size: 14px; font-weight: 600; margin-bottom: 20px;">Acteurs</h4>
                <ul class="footer-links" style="list-style: none; padding: 0;">
                    <li style="margin-bottom: 10px;"><a href="#" style="color: #94A3B8; text-decoration: none; font-size: 13px; transition: color 0.3s;"><i class="fas fa-crown" style="font-size: 10px; margin-right: 8px;"></i> Direction Générale</a></li>
                    <li style="margin-bottom: 10px;"><a href="#" style="color: #94A3B8; text-decoration: none; font-size: 13px; transition: color 0.3s;"><i class="fas fa-chart-line" style="font-size: 10px; margin-right: 8px;"></i> Conseiller Financier</a></li>
                    <li style="margin-bottom: 10px;"><a href="#" style="color: #94A3B8; text-decoration: none; font-size: 13px; transition: color 0.3s;"><i class="fas fa-building" style="font-size: 10px; margin-right: 8px;"></i> Chef de Service</a></li>
                </ul>
            </div>
            
            <div class="col-md-4 mb-4 mb-md-0">
                <h4 style="color: white; font-size: 14px; font-weight: 600; margin-bottom: 20px;">Contact & Support</h4>
                <ul class="footer-contact" style="list-style: none; padding: 0;">
                    <li style="margin-bottom: 12px; display: flex; align-items: center; gap: 10px;">
                        <i class="fas fa-envelope" style="color: #00B4DB; width: 20px;"></i>
                        <a href="mailto:support@hyperpsaro.com" style="color: #94A3B8; text-decoration: none; font-size: 13px;">support@hyperpsaro.com</a>
                    </li>
                    <li style="margin-bottom: 12px; display: flex; align-items: center; gap: 10px;">
                        <i class="fas fa-phone-alt" style="color: #00B4DB; width: 20px;"></i>
                        <span style="color: #94A3B8; font-size: 13px;">+243 812 345 678</span>
                    </li>
                    <li style="margin-bottom: 12px; display: flex; align-items: center; gap: 10px;">
                        <i class="fas fa-map-marker-alt" style="color: #00B4DB; width: 20px;"></i>
                        <span style="color: #94A3B8; font-size: 13px;">Lubumbashi, République Démocratique du Congo</span>
                    </li>
                </ul>
            </div>
        </div>
        
        {{-- Devises --}}
        <div class="footer-devises" style="text-align: center; margin: 30px 0; padding: 15px; background: rgba(255,255,255,0.03); border-radius: 12px;">
            <span style="font-size: 12px; color: #64748B; margin-right: 15px;">Devises supportées :</span>
            <span style="display: inline-flex; align-items: center; gap: 8px; margin: 0 10px;">
                <i class="fas fa-money-bill-wave" style="color: #00B4DB;"></i>
                <span style="font-weight: 600; color: white;">CDF</span>
                <span style="font-size: 11px; color: #64748B;">Franc Congolais</span>
            </span>
            <span style="display: inline-flex; align-items: center; gap: 8px; margin: 0 10px;">
                <i class="fas fa-dollar-sign" style="color: #00B4DB;"></i>
                <span style="font-weight: 600; color: white;">USD</span>
                <span style="font-size: 11px; color: #64748B;">Dollar Américain</span>
            </span>
            <span style="display: inline-flex; align-items: center; gap: 8px; margin: 0 10px;">
                <i class="fas fa-euro-sign" style="color: #00B4DB;"></i>
                <span style="font-weight: 600; color: white;">EUR</span>
                <span style="font-size: 11px; color: #64748B;">Euro</span>
            </span>
        </div>
        
        {{-- Copyright --}}
        <div class="footer-bottom" style="text-align: center; padding-top: 20px; border-top: 1px solid #1E293B;">
            <p style="font-size: 12px; color: #64748B; margin-bottom: 5px;">
                &copy; {{ date('Y') }} HYPER PSARO. Tous droits réservés.
            </p>
            <p style="font-size: 11px; color: #475569;">
                <i class="fas fa-chart-line"></i> Système Décisionnel Power BI | Sécurisé et conforme
            </p>
        </div>
    </div>
</footer>

<style>
    .footer-social a:hover {
        color: #00B4DB !important;
        transform: translateY(-2px);
    }
    .footer-links a:hover,
    .footer-contact a:hover {
        color: #00B4DB !important;
    }
    .footer-devises span {
        transition: all 0.2s;
    }
    .footer-devises span:hover {
        transform: scale(1.05);
    }
</style>