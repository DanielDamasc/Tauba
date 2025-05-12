<?php

namespace App\Http\Controllers;

use App\Services\FormaAumentadaService;
use App\Services\SimplexMaxService;
use Illuminate\Http\Request;

class SimplexController extends Controller
{
    public function __construct()
    {
        app()->instance('bigM', 1000000000); // Valor global de M.   
    }

    // Processa os dados chamando as services para executar.
    public function processar(Request $request) {

        // Recebe a forma aumentada do problema no request.
        $formaAumentada = (new FormaAumentadaService())->formaAumentada($request);

        // Deve retornar a estrutura com o problema na sua forma ótima.
        // Para quando o problema não tem solução, deve fornecer algum tratamento.
        $simplexMax = (new SimplexMaxService())->simplexMax($formaAumentada);

    }
}
