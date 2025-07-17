<?php

use App\Http\Controllers\DataGeneratorController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    
    // Rotas do gerador de dados
    Route::get('/generator', [DataGeneratorController::class, 'index'])->name('generator.index');
    Route::post('/generator/generate', [DataGeneratorController::class, 'generate'])->name('generator.generate');
});

require __DIR__.'/auth.php';