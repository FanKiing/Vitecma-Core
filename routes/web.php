<?php

use App\Http\Controllers\InspectionController;
use App\Http\Controllers\TechnicianController;
use App\Http\Controllers\MaintenanceController;
use Illuminate\Support\Facades\Route;
use App\Http\Middleware\CheckTechnicianRole;

// Redirection par défaut
Route::get('/', fn() => redirect()->route('inspections.index'));

// Routes protégées
Route::middleware(['auth', 'verified'])->group(function () {

    // Affichage du tableau de bord principal
    Route::get('/dashboard', [InspectionController::class, 'index'])
        ->name('inspections.index');

    Route::get('/home', fn() => redirect()->route('inspections.index'))
        ->name('dashboard');

    // Protection des routes Admin
    Route::middleware([CheckTechnicianRole::class])->group(function () {
        Route::post('/inspections', [InspectionController::class, 'store'])->name('inspections.store');
        
        // Mises à jour
        Route::patch('/inspections/{inspection}', [InspectionController::class, 'update'])->whereNumber('inspection')->name('inspections.update');
        Route::patch('/inspections/{inspection}/status', [InspectionController::class, 'updateStatus'])->whereNumber('inspection')->name('inspections.updateStatus');
        Route::post('/inspections/{inspection}/revert', [InspectionController::class, 'revertStatus'])->whereNumber('inspection')->name('inspections.revert');
        
        Route::get('/inspections/archive', [InspectionController::class, 'archive'])->name('inspections.archive');

        // Statistiques du jour (AJAX)
        Route::get('/inspections/daily-stats', [InspectionController::class, 'dailyStats'])
            ->name('inspections.daily-stats');
        
        // Routes de la corbeille
        Route::get('/inspections/trash', [InspectionController::class, 'trash'])->name('inspections.trash');
        Route::post('/inspections/{id}/restore', [InspectionController::class, 'restore'])->name('inspections.restore');
        Route::delete('/inspections/{id}/permanent-delete', [InspectionController::class, 'forceDestroy'])->name('inspections.forceDestroy');
        Route::delete('/inspections/trash/empty', [InspectionController::class, 'emptyTrash'])->name('inspections.emptyTrash');

        // ✅ Suppression multiple (Bulk Delete)
        Route::delete('/inspections/bulk-delete', [InspectionController::class, 'bulkDelete'])->name('inspections.bulkDelete');

        // ⚠️ Cette route générique doit toujours être déclarée APRÈS les routes
        // statiques ci-dessus (bulk-delete, trash/empty), sinon Laravel les
        // fait correspondre à {inspection} avant d'atteindre leur propre route.
        Route::delete('/inspections/{inspection}', [InspectionController::class, 'destroy'])->whereNumber('inspection')->name('inspections.destroy');

        // =========================================================
        // ✅ Routes de gestion des techniciens (Technicians)
        // =========================================================
        Route::get('/technicians', [TechnicianController::class, 'index'])->name('technicians.index');
        Route::get('/technicians/active', [TechnicianController::class, 'getActiveTechnicians'])->name('technicians.active');
        Route::post('/technicians', [TechnicianController::class, 'store'])->name('technicians.store');
        Route::get('/technicians/{id}', [TechnicianController::class, 'show'])->name('technicians.show');
        Route::put('/technicians/{id}', [TechnicianController::class, 'update'])->name('technicians.update');
        Route::delete('/technicians/{id}', [TechnicianController::class, 'destroy'])->name('technicians.destroy');
        Route::post('/technicians/{id}/toggle', [TechnicianController::class, 'toggleActive'])->name('technicians.toggle');

        // ✅ Mode maintenance (affiché uniquement pour les utilisateurs non-admin)
        Route::post('/maintenance/toggle', [MaintenanceController::class, 'toggle'])->name('maintenance.toggle');
    });

    // ✅ Route de vérification du technicien (utilisée au démarrage d'une inspection - accessible aux utilisateurs standards)
    Route::post('/technicians/verify', [TechnicianController::class, 'verify'])->name('technicians.verify');
});

require __DIR__.'/auth.php';