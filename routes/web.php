<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\KalenderController;
use App\Http\Controllers\MataKuliahController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\StatistikController;
use App\Http\Controllers\TugasController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ImportExportController;


// Auth routes
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Protected routes
Route::middleware('auth')->group(function () {
    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Mata Kuliah (Jadwal Kuliah)
    Route::resource('mata-kuliah', MataKuliahController::class)->except(['show']);
    Route::post('mata-kuliah/bulk-action', [MataKuliahController::class, 'bulkAction'])
        ->name('mata-kuliah.bulk-action');

    // Tugas
    // Tugas manual routes
    Route::get('tugas', [TugasController::class, 'index'])->name('tugas.index');
    Route::get('tugas/create', [TugasController::class, 'create'])->name('tugas.create');
    Route::post('tugas', [TugasController::class, 'store'])->name('tugas.store');
    Route::get('tugas/{tugas}', [TugasController::class, 'show'])->name('tugas.show');
    Route::get('tugas/{tugas}/edit', [TugasController::class, 'edit'])->name('tugas.edit');
    Route::put('tugas/{tugas}', [TugasController::class, 'update'])->name('tugas.update');
    Route::delete('tugas/{tugas}', [TugasController::class, 'destroy'])->name('tugas.destroy');
    Route::patch('tugas/{tugas}/progress', [TugasController::class, 'updateProgress'])->name('tugas.progress');

    // Todo
    Route::resource('todo', \App\Http\Controllers\TodoController::class);
    // Update status todo (AJAX)
    Route::patch('todo/{todo}/status', [TugasController::class, 'updateTodoStatus'])->name('todo.updateStatus');

    // Kalender
    Route::get('/kalender', [KalenderController::class, 'index'])->name('kalender.index');

    // Statistik
    Route::get('/statistik', [StatistikController::class, 'index'])->name('statistik.index');

    // Profile
    Route::get('/profil', [ProfileController::class, 'show'])->name('profile.show');
    Route::put('/profil', [ProfileController::class, 'update'])->name('profile.update');

    // About
    Route::get('/about', fn() => view('about.index'))->name('about.index');

    // ╔══════════════════════════════════════════════════════════════╗
    // ║  IMPORT & EXPORT DATA                                       ║
    // ╚══════════════════════════════════════════════════════════════╝
    // Export
    Route::prefix('import-export')->name('import-export.')->group(function () {
        // Export
        Route::get('/{module}/export', [ImportExportController::class, 'exportForm'])->name('export');
        Route::post('/{module}/export', [ImportExportController::class, 'export'])->name('export.process');
        // Import
        Route::get('/{module}/import', [ImportExportController::class, 'importForm'])->name('import');
        Route::post('/{module}/import', [ImportExportController::class, 'import'])->name('import.process');
        // Template
        Route::get('/{module}/template', [ImportExportController::class, 'downloadTemplate'])->name('template');
    });

    // Event management
    Route::resource('events', \App\Http\Controllers\EventController::class);
    // Global search (placeholder)
    Route::get(
        '/search',
        function () {
            return response()->json(['results' => []]);
        }
    )->name('global-search');
});


