<?php

use App\Http\Controllers\SimplexController;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/simplex', function () {
    return view('simplex.escolha');
})->name('simplex.escolha');

Route::get('/simplex/dados', function () {
    return view('simplex.dados');
})->name('simplex.dados');

Route::get('/simplex/montar', function (Request $request) {
    $tipo = $request->input('tipo');
    $variaveis = (int) $request->input('variaveis');
    $restricoes = (int) $request->input('restricoes');

    return view('simplex.montar', compact('tipo', 'variaveis', 'restricoes'));
})->name('simplex.montar');

// Nova rota para processar a solução
Route::post('/simplex/resolver', [SimplexController::class, 'processar'])
    ->name('simplex.resolver');

// Rota para exibir resultados (opcional, se precisar de uma URL separada)
Route::get('/simplex/resultado', function () {
    return view('simplex.resultado');
})->name('simplex.resultado');



/*
    --- COMO ACESSAR OS DADOS DO PROBLEMA ---

    $tipo = $request->input('tipo');
    $variaveis = (int) $request->input('variaveis');
    $restricoes = (int) $request->input('restricoes');
    $z = $request->input('z'); // <- ESSA LINHA É ESSENCIAL
    $restricoesData = $request->input('restricoes');
*/