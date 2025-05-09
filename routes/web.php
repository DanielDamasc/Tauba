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

// CONTROLLER PARA RESOLVER O MÉTODO
Route::post('/simplex/formatar', [SimplexController::class, 'processar'])->name('simplex.formatar');



/*
    --- COMO ACESSAR OS DADOS DO PROBLEMA ---

    $tipo = $request->input('tipo');
    $variaveis = (int) $request->input('variaveis');
    $restricoes = (int) $request->input('restricoes');
    $z = $request->input('z'); // <- ESSA LINHA É ESSENCIAL
    $restricoesData = $request->input('restricoes');
*/