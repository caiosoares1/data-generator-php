<?php

use App\Http\Controllers\DataGeneratorController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;

// Rota principal - redireciona para o gerador se logado, senão para login
Route::get('/', function () {
    return auth()->check() ? redirect()->route('generator.index') : redirect()->route('login');
});

// Health check melhorado
Route::get('/health', function() {
    try {
        // Test database connection
        DB::connection()->getPdo();
        $dbStatus = 'connected';
        
        // Test basic query
        $userCount = DB::table('users')->count();
        
        return response()->json([
            'status' => 'healthy',
            'app_port' => env('PORT', 10000),
            'db_port' => env('DB_PORT', 5432),
            'db_status' => $dbStatus,
            'user_count' => $userCount,
            'timestamp' => now()->toISOString()
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'unhealthy',
            'error' => $e->getMessage(),
            'app_port' => env('PORT', 10000),
            'db_port' => env('DB_PORT', 5432),
            'timestamp' => now()->toISOString()
        ], 503);
    }
});

// Rotas protegidas por autenticação
Route::middleware('auth')->group(function () {
    Route::get('/generator', [DataGeneratorController::class, 'index'])->name('generator.index');
    Route::post('/generator/generate', [DataGeneratorController::class, 'generate'])->name('generator.generate');

    // Rotas para Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';