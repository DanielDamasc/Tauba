<?php

namespace App\Http\Controllers;

use App\Services\FormaAumentadaService;
use App\Services\SolverSimplexMaxService;
use App\Services\ZFormalizadaService;
use Illuminate\Http\Request;

class SimplexController extends Controller
{
    public function __construct()
    {
        app()->instance('bigM', 1000000000); // Valor global de M.   
    }

    // Processa os dados chamando as services para executar.
    public function processar(Request $request)
    {

        try {
            // Coloca o request atual na session.
            $request->session()->put('tipo', $request->input('tipo'));
            $request->session()->put('variaveis', $request->input('variaveis'));
            $request->session()->put('restricoes', $request->input('restricoes'));
            $request->session()->put('z', $request->input('z'));

            // Recebe a forma aumentada do problema no request.
            $formaAumentada = (new FormaAumentadaService())->formaAumentada($request);

            // Recebe a função objetivo sem variáveis artificiais.
            $zFormalizada = (new ZFormalizadaService())->zFormalizada($formaAumentada);

            // Recebe a estrutura da solução ótima aplicando o método simplex.
            $resultado = (new SolverSimplexMaxService())->solverSimplex($zFormalizada);

            return view('simplex.resultado', $resultado);
        } catch (\Exception) {
            // Em caso de erro, redireciona de volta com a mensagem de erro (Toastr).
            $tipo = $request->input('tipo');
            $variaveis = (int) $request->input('variaveis');
            $restricoesDados = $request->input('restricoes');
            $restricoes = count($restricoesDados);
            $z = $request->input('z');

            return view('simplex.montar', compact('tipo', 'variaveis', 'restricoesDados', 'restricoes', 'z') + ['error' => 'Erro: problema ilimitado.']);
        }
    }
}
