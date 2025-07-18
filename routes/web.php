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
    $health = [
        'status' => 'healthy',
        'app_port' => env('PORT', 10000),
        'db_port' => env('DB_PORT', 5432),
        'timestamp' => now()->toISOString()
    ];
    
    try {
        // Configure SSL connection
        $dsn = 'pgsql:host='.env('DB_HOST').';port='.env('DB_PORT').';dbname='.env('DB_DATABASE').';sslmode=require';
        $pdo = new PDO($dsn, env('DB_USERNAME'), env('DB_PASSWORD'), [
            PDO::ATTR_TIMEOUT => 30,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_PERSISTENT => false
        ]);
        
        $health['db_status'] = 'connected';
        $health['user_count'] = DB::table('users')->count();
        
    } catch (\Exception $e) {
        $health['status'] = 'degraded';
        $health['db_status'] = 'disconnected';
        $health['db_error'] = $e->getMessage();
        
        return response()->json($health, 200);
    }
    
    return response()->json($health);
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