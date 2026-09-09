<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ManifestacaoController as AdminManifestacaoController;
use App\Http\Controllers\ManifestacaoController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('LandingPage.index');
})->name('home');

Route::get('/registrar', [ManifestacaoController::class, 'create'])->name('manifestacoes.create');
Route::post('/manifestacoes', [ManifestacaoController::class, 'store'])->name('manifestacoes.store');
Route::get('/manifestacoes/{protocolo}/sucesso', [ManifestacaoController::class, 'sucesso'])->name('manifestacoes.sucesso');
Route::get('/acompanhar', [ManifestacaoController::class, 'acompanhar'])->name('manifestacoes.acompanhar');

// TODO: aplicar middleware SSO neste grupo antes de ir para produção.
Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminManifestacaoController::class, 'index'])->name('manifestacoes.index');
    Route::get('/atendimentos', [AdminManifestacaoController::class, 'atendimentos'])->name('manifestacoes.atendimentos');
    Route::get('/historico', [AdminManifestacaoController::class, 'historico'])->name('manifestacoes.historico');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/manifestacoes/{manifestacao}', [AdminManifestacaoController::class, 'show'])->name('manifestacoes.show');
    Route::put('/manifestacoes/{manifestacao}', [AdminManifestacaoController::class, 'update'])->name('manifestacoes.update');
    Route::delete('/manifestacoes/{manifestacao}', [AdminManifestacaoController::class, 'destroy'])->name('manifestacoes.destroy');
});
