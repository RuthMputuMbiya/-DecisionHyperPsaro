<?php
// routes/web.php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Auth\SessionController;
use App\Http\Controllers\Auth\ChangePasswordController;

// ==============================================
// DIRECTION GÉNÉRALE (DG)
// ==============================================
use App\Http\Controllers\Direction\UserController;
use App\Http\Controllers\Direction\ServiceController;
use App\Http\Controllers\Direction\IndicateurController;
use App\Http\Controllers\Direction\DecisionController;
use App\Http\Controllers\Direction\ReportController;
use App\Http\Controllers\Direction\DashboardController as DirectionDashboardController;

// ==============================================
// CONSEILLER FINANCIER
// ==============================================
use App\Http\Controllers\Finance\DashboardController as FinanceDashboardController;
use App\Http\Controllers\Finance\AnalyseController;
use App\Http\Controllers\Finance\SimulationController;
use App\Http\Controllers\Finance\ComparisonController;
use App\Http\Controllers\Finance\ReportingController;

// ==============================================
// CHEF DE SERVICE
// ==============================================
use App\Http\Controllers\Service\DashboardController as ServiceDashboardController;
use App\Http\Controllers\Service\VenteController;
use App\Http\Controllers\Service\AchatController;
use App\Http\Controllers\Service\DepenseController;
use App\Http\Controllers\Service\InvestissementController;
use App\Http\Controllers\Service\PerformanceController;

// ==============================================
// ROUTES PUBLIQUES
// ==============================================

// Page d'accueil
Route::get('/', function () {
    return view('welcome');
})->name('welcome');

// ==============================================
// ROUTES D'AUTHENTIFICATION
// ==============================================

// Connexion
Route::get('/login', [SessionController::class, 'showLoginForm'])->name('login');
Route::post('/login', [SessionController::class, 'login'])->name('login.post');
Route::post('/logout', [SessionController::class, 'logout'])->name('logout');

// Changement de mot de passe (première connexion)
Route::middleware(['auth'])->group(function () {
    Route::get('/password/change', [ChangePasswordController::class, 'showChangeForm'])->name('password.change');
    Route::post('/password/change', [ChangePasswordController::class, 'change'])->name('password.change.submit');
});

// ==============================================
// ROUTES PROTÉGÉES (AUTHENTIFICATION REQUISE)
// ==============================================
Route::middleware(['auth'])->group(function () {
    
    // DASHBOARD PRINCIPAL QUI REDIRIGE SELON LE RÔLE
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // Changement de devise (tous les utilisateurs)
    Route::post('/change-currency', [DashboardController::class, 'changeCurrency'])->name('change-currency');
    
    // ==============================================
    // 1. ROUTES DIRECTION GÉNÉRALE
    // ==============================================
    Route::prefix('direction')->name('direction.')->group(function () {
        
        // Dashboard DG
        Route::get('/dashboard', [DirectionDashboardController::class, 'index'])->name('dashboard');
        
        // Gestion des utilisateurs
        Route::resource('users', UserController::class);
        Route::post('/users/{id}/toggle-status', [UserController::class, 'toggleStatus'])->name('users.toggle-status');
        Route::post('/users/{id}/reset-password', [UserController::class, 'resetPassword'])->name('users.reset-password');
        Route::get('/users/{id}/delete', [UserController::class, 'destroy'])->name('users.delete');
        
        // Gestion des services
        Route::resource('services', ServiceController::class);
        Route::post('/services/{id}/toggle-status', [ServiceController::class, 'toggleStatus'])->name('services.toggle-status');
        Route::get('/services/{id}/delete', [ServiceController::class, 'destroy'])->name('services.delete');
        
        // Gestion des indicateurs
        Route::resource('indicateurs', IndicateurController::class);
        Route::post('/indicateurs/{id}/toggle-status', [IndicateurController::class, 'toggleStatus'])->name('indicateurs.toggle-status');
        Route::get('/indicateurs/{id}/check-alert', [IndicateurController::class, 'checkAlert'])->name('indicateurs.check-alert');
        Route::get('/indicateurs/{id}/delete', [IndicateurController::class, 'destroy'])->name('indicateurs.delete');
        Route::get('/indicateurs/predefined/{code}', [IndicateurController::class, 'getPredefined'])->name('indicateurs.predefined');

        // ============================================
        // 🔥 ROUTES DES DÉCISIONS - CORRIGÉES
        // ============================================
        
        // 🔥 ROUTES GET (POUR AFFICHER LES PAGES)
        Route::get('/decisions', [DecisionController::class, 'index'])->name('decisions.index');
        Route::get('/decisions/generate', [DecisionController::class, 'generate'])->name('decisions.generate');
        Route::get('/decisions/export', [DecisionController::class, 'export'])->name('decisions.export');
        
        // 🔥 ROUTE SHOW (DOIT ÊTRE EN DERNIER)
        Route::get('/decisions/{id}', [DecisionController::class, 'show'])->name('decisions.show');
        
        // 🔥 ROUTES POST (POUR VALIDER ET REJETER)
        Route::post('/decisions/{id}/validate', [DecisionController::class, 'validateDecision'])->name('decisions.validate');
        Route::post('/decisions/{id}/reject', [DecisionController::class, 'rejectDecision'])->name('decisions.reject');
        
        // Rapports
        Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
        Route::get('/reports/{reportId}/generate', [ReportController::class, 'generate'])->name('reports.generate');
    });
    
    // ==============================================
    // 2. ROUTES CONSEILLER FINANCIER
    // ==============================================
    Route::prefix('finance')->name('finance.')->group(function () {
        
        // Dashboard Finance
        Route::get('/dashboard', [FinanceDashboardController::class, 'index'])->name('dashboard');
        
        // Analyses financières
        Route::prefix('analyses')->name('analyses.')->group(function () {
            Route::get('/', [AnalyseController::class, 'index'])->name('index');
            Route::get('/tendances', [AnalyseController::class, 'tendances'])->name('tendances');
            Route::get('/previsions', [AnalyseController::class, 'previsions'])->name('previsions');
        });
        
        // Simulations
        Route::prefix('simulations')->name('simulations.')->group(function () {
            Route::get('/', [SimulationController::class, 'index'])->name('index');
            Route::get('/create', [SimulationController::class, 'create'])->name('create');
            Route::post('/store', [SimulationController::class, 'store'])->name('store');
            Route::get('/{id}', [SimulationController::class, 'show'])->name('show');
            Route::get('/history', [SimulationController::class, 'history'])->name('history');
            Route::post('/calculate', [SimulationController::class, 'calculate'])->name('calculate');
            Route::post('/save', [SimulationController::class, 'saveSimulation'])->name('save');
            Route::get('/{id}/delete', [SimulationController::class, 'destroy'])->name('destroy');
            Route::get('/{id}/duplicate', [SimulationController::class, 'duplicate'])->name('duplicate');
            Route::get('/export/history', [SimulationController::class, 'exportHistory'])->name('export.history');
        });
        
        // Comparaisons
        Route::prefix('comparaisons')->name('comparaisons.')->group(function () {
            Route::get('/', [ComparisonController::class, 'index'])->name('index');
            Route::get('/periodes', [ComparisonController::class, 'periodes'])->name('periodes');
            Route::get('/services', [ComparisonController::class, 'services'])->name('services');
            Route::get('/indicateurs', [ComparisonController::class, 'indicateurs'])->name('indicateurs');
            Route::get('/chart-data', [ComparisonController::class, 'chartData'])->name('chart-data');
        });
        
        // Reporting & Export
        Route::prefix('reporting')->name('reporting.')->group(function () {
            Route::get('/', [ReportingController::class, 'index'])->name('index');
            Route::post('/export', [ReportingController::class, 'export'])->name('export');
        });
    });
    
    // ==============================================
    // 3. ROUTES CHEF DE SERVICE
    // ==============================================
    Route::prefix('service')->name('service.')->group(function () {
        
        // 🔥 DASHBOARD - CORRIGÉ
        Route::get('/dashboard', [ServiceDashboardController::class, 'index'])->name('dashboard');
        Route::get('/service.dashboard', [ServiceDashboardController::class, 'index'])->name('service.dashboard');
        
        Route::get('/performances', [PerformanceController::class, 'index'])->name('performances.index');
        
        Route::prefix('ventes')->name('ventes.')->group(function () {
            Route::get('/', [VenteController::class, 'index'])->name('index');
            Route::get('/create', [VenteController::class, 'create'])->name('create');
            Route::post('/store', [VenteController::class, 'store'])->name('store');
            Route::get('/{id}', [VenteController::class, 'show'])->name('show');
            Route::get('/{id}/edit', [VenteController::class, 'edit'])->name('edit');
            Route::put('/{id}', [VenteController::class, 'update'])->name('update');
            Route::get('/export', [VenteController::class, 'export'])->name('export');
            Route::get('/{id}/delete', [VenteController::class, 'destroy'])->name('delete');
        });
        
        Route::prefix('achats')->name('achats.')->group(function () {
            Route::get('/', [AchatController::class, 'index'])->name('index');
            Route::get('/create', [AchatController::class, 'create'])->name('create');
            Route::post('/store', [AchatController::class, 'store'])->name('store');
            Route::get('/{id}', [AchatController::class, 'show'])->name('show');
            Route::get('/{id}/edit', [AchatController::class, 'edit'])->name('edit');
            Route::put('/{id}', [AchatController::class, 'update'])->name('update');
            Route::get('/export', [AchatController::class, 'export'])->name('export');
            Route::get('/{id}/delete', [AchatController::class, 'destroy'])->name('delete');
        });
        
        //  DÉPENSES - AJOUTER LA ROUTE EXPORT
    Route::prefix('depenses')->name('depenses.')->group(function () {
        Route::get('/', [DepenseController::class, 'index'])->name('index');
        Route::get('/create', [DepenseController::class, 'create'])->name('create');
        Route::post('/store', [DepenseController::class, 'store'])->name('store');
        Route::get('/{id}', [DepenseController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [DepenseController::class, 'edit'])->name('edit');
        Route::put('/{id}', [DepenseController::class, 'update'])->name('update');
        Route::get('/export', [DepenseController::class, 'export'])->name('export');  // 🔥 AJOUTER CETTE LIGNE
        Route::get('/{id}/delete', [DepenseController::class, 'destroy'])->name('delete');
    });
        
        Route::prefix('investissements')->name('investissements.')->group(function () {
            Route::get('/', [InvestissementController::class, 'index'])->name('index');
            Route::get('/create', [InvestissementController::class, 'create'])->name('create');
            Route::post('/store', [InvestissementController::class, 'store'])->name('store');
            Route::get('/{id}', [InvestissementController::class, 'show'])->name('show');
            Route::get('/{id}/edit', [InvestissementController::class, 'edit'])->name('edit');
            Route::put('/{id}', [InvestissementController::class, 'update'])->name('update');
            Route::get('/export', [InvestissementController::class, 'export'])->name('export');
            Route::get('/{id}/delete', [InvestissementController::class, 'destroy'])->name('delete');
             // 🔥 PERFORMANCES
    Route::get('/performances', [PerformanceController::class, 'index'])->name('performances.index');
    Route::get('/performances/export', [PerformanceController::class, 'export'])->name('performances.export');  // 🔥 AJOUTÉ
    
    // Ventes
    Route::prefix('ventes')->name('ventes.')->group(function () {
        Route::get('/', [VenteController::class, 'index'])->name('index');
        Route::get('/create', [VenteController::class, 'create'])->name('create');
        Route::post('/store', [VenteController::class, 'store'])->name('store');
        Route::get('/{id}', [VenteController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [VenteController::class, 'edit'])->name('edit');
        Route::put('/{id}', [VenteController::class, 'update'])->name('update');
        Route::get('/export', [VenteController::class, 'export'])->name('export');
        Route::get('/{id}/delete', [VenteController::class, 'destroy'])->name('delete');
    });
       
       
       
       
            });
    });
});

// ==============================================
// ROUTE DE TEST
// ==============================================
Route::get('/test', function () {
    return view('test');
});