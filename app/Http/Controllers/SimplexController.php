<?php

namespace App\Http\Controllers;

use App\Services\FormaAumentadaService;
use App\Services\SolverSimplexService;
use App\Services\ZFormalizadaService;
use App\Services\EstruturaGraficoService;
use App\Services\GerarGraficoService;
use App\Services\PutOnSessionService;
use App\Services\BranchAndBoundService; // Adicionado
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
        // Pega os dados do request.
        $tipo = $request->input('tipo');
        $variaveis = (int) $request->input('variaveis');
        $restricoesDados = $request->input('restricoes');
        $restricoes = count($restricoesDados);
        $z = $request->input('z');

        // Coloca o request atual na session.
        (new PutOnSessionService())->putOnSession($request);

        // Lógica para a solução tabular.
        if ($request["metodo"] == "algebrica") {
            try {
                // Recebe a forma aumentada do problema no request.
                $formaAumentada = (new FormaAumentadaService())->formaAumentada($request);

                // Recebe a função objetivo sem variáveis artificiais.
                $zFormalizada = (new ZFormalizadaService())->zFormalizada($formaAumentada);

                // Recebe a estrutura da solução ótima aplicando o método simplex.
                $resultado = (new SolverSimplexService())->solverSimplex($zFormalizada, strtolower($tipo));
                $resultado['is_branch_and_bound'] = false;
                return view('simplex.resultado', $resultado);

            } catch (\Exception $e) {

                return view('simplex.montar', compact('tipo', 'variaveis', 'restricoesDados', 'restricoes', 'z') + ['error' => 'Erro: ' . $e->getMessage()]);
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
            $fileName = (new GerarGraficoService())->gerarGrafico($estruturaGrafico);

            // Pega nome do arquivo (str).
            $nome = $fileName["nome"];

            // Retorna nome do arquivo para view que mostra a imagem.
            return view('simplex.geometrica', compact('nome'));
        }

        if ($request["metodo"] == "inteira") {
            // Recebe a forma aumentada do problema no request.
            $formaAumentada = (new FormaAumentadaService())->formaAumentada($request);

            // Instancia e usa o BranchAndBoundService
            $branchAndBoundService = app(BranchAndBoundService::class);
            $resultado = $branchAndBoundService->solve($formaAumentada, $tipo, $variaveis);
            $resultado['is_branch_and_bound'] = true;
            return view('simplex.resultado', $resultado);
        }

        return view('simplex.montar', compact('tipo', 'variaveis', 'restricoesDados', 'restricoes', 'z') + ['error' => 'Erro: método de solução indefinido.']);
    }
}
