<?php

namespace App\Http\Controllers;

use App\Services\FormaAumentadaService;
use App\Services\SolverSimplexMaxService;
use App\Services\ZFormalizadaService;
use App\Services\EstruturaGraficoService;
use App\Services\GerarGraficoService;
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
        // Em caso de erro, redireciona de volta com a mensagem de erro (Toastr).
        $tipo = $request->input('tipo');
        $variaveis = (int) $request->input('variaveis');
        $restricoesDados = $request->input('restricoes');
        $restricoes = count($restricoesDados);
        $z = $request->input('z');

        // Lógica para a solução tabular.
        if ($request["metodo"] == "algebrica") {
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

                return view('simplex.montar', compact('tipo', 'variaveis', 'restricoesDados', 'restricoes', 'z') + ['error' => 'Erro: problema ilimitado.']);
            }
        }

        // Lógica para a solução geométrica.
        if ($request["metodo"] == "geometrica") {

            // Se for maior do que 2, retorna com erro, não pode fazer solução geométrica.
            if ($variaveis > 2) {
                return view('simplex.montar', compact('tipo', 'variaveis', 'restricoesDados', 'restricoes', 'z') + ['error' => 'Erro: solução geométrica deve conter no máximo duas variáveis.']);
            }

            // Captura as restrições e joga na Service.
            $restricoesData = $request['restricoes'];

            // Estrutura os dados usados na solução gráfica.
            $estruturaGrafico = (new EstruturaGraficoService())->estruturaGrafico($restricoesData);

            // Service integrada com python que gera solução gráfica.
            $caminho = (new GerarGraficoService())->gerarGrafico($estruturaGrafico);

            // Retorna caminho para view que mostra a imagem.
            dd($caminho["caminho"]);
        }
        
        return view('simplex.montar', compact('tipo', 'variaveis', 'restricoesDados', 'restricoes', 'z') + ['error' => 'Erro: método de solução indefinido.']);
    }
}
