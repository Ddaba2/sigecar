<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\GestionnaireController;
use App\Http\Controllers\MarketeurController;

// Routes d'authentification
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Route du dashboard par défaut avec redirection selon le rôle
Route::get('/', function () {
    // Vérification de l'authentification
    if (Auth::check()) {
        $user = Auth::user();
        /** @var \App\Models\User $user */
        // Redirection selon le rôle de l'utilisateur
        if ($user->isAdmin()) return redirect()->route('admin.dashboard');
        if ($user->isGestionnaire()) return redirect()->route('gestionnaire.dashboard');
        if ($user->isMarketeur()) return redirect()->route('marketeur.dashboard');
    }
    return view('auth.login');
});

// Admin routes
Route::prefix('admin')->name('admin.')->middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/', [AdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/users', [AdminController::class, 'users'])->name('users');
    Route::get('/users/add', [AdminController::class, 'addUser'])->name('add-user');
    Route::post('/users', [AdminController::class, 'storeUser'])->name('store-user');
    Route::get('/users/{id}/edit', [AdminController::class, 'editUser'])->name('edit-user');
    Route::put('/users/{id}', [AdminController::class, 'updateUser'])->name('update-user');
    Route::delete('/users/{id}', [AdminController::class, 'deleteUser'])->name('delete-user');
    Route::patch('/users/{id}/disable', [AdminController::class, 'disableUser'])->name('disable-user');
    Route::patch('/users/{id}/activate', [AdminController::class, 'activateUser'])->name('activate-user');
    Route::get('/depot', [AdminController::class, 'depot'])->name('depot');
    Route::get('/transport', [AdminController::class, 'transport'])->name('transport');
    Route::get('/cessions', [AdminController::class, 'cessions'])->name('cessions');
});

// Gestionnaire routes
Route::prefix('gestionnaire')->name('gestionnaire.')->middleware(['auth', 'role:gestionnaire'])->group(function () {
    Route::get('/', [GestionnaireController::class, 'dashboard'])->name('dashboard');
    Route::get('/operations', [GestionnaireController::class, 'operations'])->name('operations');

    // Dépotage
    Route::get('/depotage/create', [GestionnaireController::class, 'createDepotage'])->name('depotage.create');
    Route::post('/depotage', [GestionnaireController::class, 'storeDepotage'])->name('depotage.store');
    Route::post('/depotage/{depotage}/acquitter', [GestionnaireController::class, 'acquitterDepotage'])->name('depotage.acquitter');

    // Chargement
    Route::get('/chargement/create', [GestionnaireController::class, 'createChargement'])->name('chargement.create');
    Route::post('/chargement', [GestionnaireController::class, 'storeChargement'])->name('chargement.store');

    Route::get('/documents/{type}/{id}/download', [GestionnaireController::class, 'downloadDocument'])->name('document.download');

    // Cession
    Route::get('/cession/create', [GestionnaireController::class, 'createCession'])->name('cession.create');
    Route::post('/cession', [GestionnaireController::class, 'storeCession'])->name('cession.store');

    Route::get('/stocks', [GestionnaireController::class, 'stocks'])->name('stocks');
    Route::get('/stocks/tous', [GestionnaireController::class, 'stocksTous'])->name('stocks.tous');
    Route::get('/rapports', [GestionnaireController::class, 'rapports'])->name('rapports');
    Route::get('/rapports/export/csv', [GestionnaireController::class, 'exportRapportCsv'])->name('rapports.export.csv');
    Route::get('/rapports/export/pdf', [GestionnaireController::class, 'exportRapportPdf'])->name('rapports.export.pdf');
    Route::get('/parametres', [GestionnaireController::class, 'settings'])->name('settings');
});

// Marketeur routes
Route::prefix('marketeur')->name('marketeur.')->middleware(['auth', 'role:marketeur'])->group(function () {
    Route::get('/', [MarketeurController::class, 'dashboard'])->name('dashboard');
    Route::get('/operations', [MarketeurController::class, 'operations'])->name('operations');
    Route::get('/cessions', [MarketeurController::class, 'cessions'])->name('cessions');
    Route::get('/cessions/{id}', [MarketeurController::class, 'showCession'])->name('cession.show');
});
