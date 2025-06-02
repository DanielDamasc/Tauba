<?php

namespace App\Http\Controllers;

use App\Services\FormaAumentadaService;
use App\Services\SolverSimplexMaxService;
use App\Services\SolverSimplexService;
use App\Services\ZFormalizadaService;
use Illuminate\Http\Request;

class SimplexController extends Controller
{
    public function __construct()
    {
        app()->instance('bigM', 1000000000); // Valor global de M.   
    }

    // Processa os dados chamando as services para executar.
    public function processar(Request $request) {

        /// Validar o tipo de objetivo
        $tipoObjetivo = strtolower($request->input('tipo'));
        if ($tipoObjetivo !== 'max' && $tipoObjetivo !== 'min') {
            // Retornar um erro ou uma view com mensagem de erro se o tipo for inválido
            // Por simplicidade, aqui vamos lançar uma exceção, mas em produção
            // um tratamento mais amigável seria ideal.
            throw new \InvalidArgumentException("Tipo de objetivo inválido fornecido na requisição. Deve ser 'max' ou 'min'.");
        }

        // 1. Obter a forma aumentada do problema.
        // A FormaAumentadaService prepara a tabela inicial do simplex,
        // adicionando variáveis de folga, excesso e artificiais conforme necessário.
        $formaAumentada = (new FormaAumentadaService())->formaAumentada($request);

        // 2. Formalizar a função objetivo Z.
        // A ZFormalizadaService ajusta a linha da função objetivo (linha Z)
        // para eliminar os termos Big M caso variáveis artificiais estejam na base inicial.
        $zFormalizada = (new ZFormalizadaService())->zFormalizada($formaAumentada);

        // 3. Resolver o problema usando o SolverSimplexService.
        // O SolverSimplexService executa as iterações do algoritmo Simplex.
        // Agora, passamos a tabela Z formalizada e o tipo de objetivo ('max' ou 'min')
        // que foi obtido da requisição.
        $resultado = (new SolverSimplexService())->solverSimplex($zFormalizada, $tipoObjetivo);

        // 4. Retornar a view com os resultados.
        // A view 'simplex.resultado' será responsável por exibir as iterações e a solução final.
        return view('simplex.resultado', $resultado);
    }
}
