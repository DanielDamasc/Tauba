<?php

use App\Http\Controllers\SimplexController;
use App\Http\Controllers\MontarController;
use App\Http\Controllers\ImportController;
use App\Http\Controllers\DownloadController;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

Route::get('/', function () {
    return view('welcome');
})->name('simplex.index');

Route::get('/simplex/info', function () {
    return view('simplex.info');
})->name('simplex.info');

Route::get('/simplex', function () {
    return view('simplex.escolha');
})->name('simplex.escolha');

Route::get('/simplex/dados', function () {
    return view('simplex.dados');
})->name('simplex.dados');

// Rota que trata o request no controller.
Route::get('/simplex/montar', [MontarController::class, 'montar'])
    ->name('simplex.montar');

// Nova rota para processar a solução.
Route::post('/simplex/resolver', [SimplexController::class, 'processar'])
    ->name('simplex.resolver');

// Rota do import de problemas existentes.
Route::post('/simplex/importar', [ImportController::class, 'importar'])
    ->name('simplex.importar');

// Rota para download de problemas.
Route::get('/simplex/download', [DownloadController::class, 'download'])
    ->name('simplex.download');


/*
    --- COMO ACESSAR OS DADOS DO PROBLEMA ---

    $tipo = $request->input('tipo');
    $variaveis = (int) $request->input('variaveis');
    $restricoes = (int) $request->input('restricoes');
    $z = $request->input('z'); // <- ESSA LINHA É ESSENCIAL
    $restricoesData = $request->input('restricoes');
*/