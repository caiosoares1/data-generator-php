<?php

use App\Http\Controllers\DataGeneratorController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;

// Rota principal - redireciona para o gerador se logado, senão para login
Route::get('/', function () {
    return auth()->check() ? redirect()->route('generator.index') : redirect()->route('login');
});



Route::get('/health', function() {
    try {
        DB::connection()->getPdo();
        return response()->json([
            'status' => 'healthy',
            'services' => [
                'database' => true,
                'web' => true
            ]
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'unhealthy',
            'error' => $e->getMessage()
        ], 503);
    }
});

// Rotas protegidas por autenticação
Route::middleware('auth')->group(function () {
    Route::get('/generator', [DataGeneratorController::class, 'index'])->name('generator.index');
    Route::post('/generator/generate', [DataGeneratorController::class, 'generate'])->name('generator.generate');

    // Rotas para Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';