<?php

namespace App\Http\Controllers;

use App\Services\FormaAumentadaService;
use App\Services\SimplexMaxService;
use Illuminate\Http\Request;

class SimplexController extends Controller
{
    // PROCESSA OS DADOS E CHAMA AS SERVICES PARA FORMA PADRÃO E RESOLUÇÃO DO ALGORITMO
    public function processar(Request $request) {

        $formaAumentada = (new FormaAumentadaService())->formaAumentada($request);

        // deve retornar a estrutura com o problema na sua forma ótima.
        // para quando o problema não pode ser resolvido, deve retornar uma mensagem de erro.
        $simplexMax = (new SimplexMaxService())->simplexMax($formaAumentada);

    }
}
