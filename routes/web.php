<?php

use App\Http\Controllers\InspectionController;
use Illuminate\Support\Facades\Route;
use App\Http\Middleware\CheckTechnicianRole;

// إعادة التوجيه الافتراضية
Route::get('/', fn() => redirect()->route('inspections.index'));

// الروابط المحمية
Route::middleware(['auth', 'verified'])->group(function () {

    // عرض اللوحة الرئيسية
    Route::get('/dashboard', [InspectionController::class, 'index'])
        ->name('inspections.index');

    Route::get('/home', fn() => redirect()->route('inspections.index'))
        ->name('dashboard');

    // حماية روابط الأدمن
    Route::middleware([CheckTechnicianRole::class])->group(function () {
        Route::post('/inspections', [InspectionController::class, 'store'])->name('inspections.store');
        
        // استخدام التحديثات
        Route::patch('/inspections/{inspection}', [InspectionController::class, 'update'])->name('inspections.update');
        Route::patch('/inspections/{inspection}/status', [InspectionController::class, 'updateStatus'])->name('inspections.updateStatus');
        Route::post('/inspections/{inspection}/revert', [InspectionController::class, 'revertStatus'])->name('inspections.revert');
        
        Route::get('/inspections/archive', [InspectionController::class, 'archive'])->name('inspections.archive');

        // إحصائيات اليوم (AJAX)
        Route::get('/inspections/daily-stats', [InspectionController::class, 'dailyStats'])
            ->name('inspections.daily-stats');
        
        // مسارات السلة
        Route::get('/inspections/trash', [InspectionController::class, 'trash'])->name('inspections.trash');
        Route::post('/inspections/{id}/restore', [InspectionController::class, 'restore'])->name('inspections.restore');
        Route::delete('/inspections/{id}/permanent-delete', [InspectionController::class, 'forceDestroy'])->name('inspections.forceDestroy');
        Route::delete('/inspections/{inspection}', [InspectionController::class, 'destroy'])->name('inspections.destroy');
        Route::delete('/inspections/trash/empty', [InspectionController::class, 'emptyTrash'])->name('inspections.emptyTrash');

    });
});

require __DIR__.'/auth.php';