<?php

namespace App\Services;

use App\Services\SolverSimplexService;
use App\Services\FormaAumentadaService;
use App\Services\ZFormalizadaService;

class BranchAndBoundService
{
    private $solverSimplexService;
    private $formaAumentadaService;
    private $zFormalizadaService;
    private $tipoObjetivo;
    private $variaveisOriginais;
    private $melhorSolucaoInteira = null;
    private $melhorValorZ = null;
    private $iteracoesBranchAndBound = [];
    private $nodeIdCounter = 0;

    public function __construct(
        SolverSimplexService $solverSimplexService,
        FormaAumentadaService $formaAumentadaService,
        ZFormalizadaService $zFormalizadaService
    ) {
        $this->solverSimplexService = $solverSimplexService;
        $this->formaAumentadaService = $formaAumentadaService;
        $this->zFormalizadaService = $zFormalizadaService;
    }

    public function solve(array $problemaOriginal, string $tipoObjetivo, int $variaveisOriginais)
    {
        $this->tipoObjetivo = strtolower($tipoObjetivo);
        $this->variaveisOriginais = $variaveisOriginais;
        $this->melhorValorZ = ($this->tipoObjetivo === 'max') ? -INF : INF;

        // Inicializa a pilha de nós a processar com o problema raiz
        $nodesToProcess = [];
        array_push($nodesToProcess, [
            'problema' => $problemaOriginal,
            'parentId' => 0,
            'branchDescription' => 'Raiz'
        ]);

        // Loop principal - processa nós enquanto a pilha não estiver vazia
        while (!empty($nodesToProcess)) {
            // Pega o próximo nó da pilha (estratégia LIFO - aprofunda a árvore)
            $currentNodeData = array_pop($nodesToProcess);
            $problema = $currentNodeData['problema'];
            
            $this->nodeIdCounter++;
            $currentNodeId = $this->nodeIdCounter;

            // 1. Resolver o problema relaxado (PL)
            // É crucial não reutilizar a variável $zFormalizada, pois ela é modificada por referência implicitamente
            $problemaParaResolver = unserialize(serialize($problema)); // Cópia profunda para evitar alterações indesejadas
            $zFormalizada = $this->zFormalizadaService->zFormalizada($problemaParaResolver);
            $resultadoSimplex = $this->solverSimplexService->solverSimplex($zFormalizada, $this->tipoObjetivo);

            $logNode = [
                'id' => $currentNodeId,
                'parentId' => $currentNodeData['parentId'],
                'branch' => $currentNodeData['branchDescription'],
                'resultadoSimplex' => $resultadoSimplex,
                'status' => '',
                'motivoPoda' => ''
            ];

            // 2. Analisar a solução do Simplex
            
            // Poda por inviabilidade
            if ($resultadoSimplex['status'] !== 'otimo') {
                $logNode['status'] = 'podado_por_inviabilidade';
                $logNode['motivoPoda'] = 'O subproblema relaxado é inviável ou ilimitado (' . $resultadoSimplex['status'] . ').';
                $this->iteracoesBranchAndBound[] = $logNode;
                continue; // Pula para o próximo nó na pilha
            }

            $solucao = $resultadoSimplex['solucao'];
            $valorZ = $solucao['Z'];

            // Poda por limite
            if (($this->tipoObjetivo === 'max' && $valorZ <= $this->melhorValorZ) || 
                ($this->tipoObjetivo === 'min' && $valorZ >= $this->melhorValorZ)) {
                $logNode['status'] = 'podado_por_limite';
                $logNode['motivoPoda'] = "O valor de Z ({$valorZ}) não é melhor que a solução inteira atual ({$this->melhorValorZ}).";
                $this->iteracoesBranchAndBound[] = $logNode;
                continue;
            }
            
            // 3. Verificar se a solução é inteira
            $variavelNaoInteiraIndex = $this->encontrarVariavelNaoInteira($solucao);

            if ($variavelNaoInteiraIndex === null) {
                // Solução inteira encontrada
                $logNode['status'] = 'solucao_inteira_encontrada';
                $this->melhorValorZ = $valorZ;
                $this->melhorSolucaoInteira = $solucao;
                $logNode['motivoPoda'] = "Nova melhor solução inteira encontrada. Limite atualizado para {$this->melhorValorZ}.";
                $this->iteracoesBranchAndBound[] = $logNode;
                continue;
            }

            // 4. Se não for inteira, ramificar (branch)
            $logNode['status'] = 'ramificando';
            $this->iteracoesBranchAndBound[] = $logNode;
            
            $valorNaoInteiro = $solucao['x' . ($variavelNaoInteiraIndex)];
            
            // Cria os dois novos nós e os adiciona na pilha
            
            // Branch 1: x_i >= ceil(valor) - Adicionado primeiro para ser processado por último (busca em profundidade)
            $problemaBranch2 = $this->adicionarRestricao($problema, $variavelNaoInteiraIndex, ceil($valorNaoInteiro), '>=');
            array_push($nodesToProcess, [
                'problema' => $problemaBranch2,
                'parentId' => $currentNodeId,
                'branchDescription' => "x{$variavelNaoInteiraIndex} >= " . ceil($valorNaoInteiro)
            ]);

            // Branch 2: x_i <= floor(valor)
            $problemaBranch1 = $this->adicionarRestricao($problema, $variavelNaoInteiraIndex, floor($valorNaoInteiro), '<=');
            array_push($nodesToProcess, [
                'problema' => $problemaBranch1,
                'parentId' => $currentNodeId,
                'branchDescription' => "x{$variavelNaoInteiraIndex} <= " . floor($valorNaoInteiro)
            ]);
        }

        return [
            'solucao' => $this->melhorSolucaoInteira,
            'status' => $this->melhorSolucaoInteira ? 'otimo_inteiro' : 'sem_solucao_inteira',
            'iteracoesBranchAndBound' => $this->iteracoesBranchAndBound,
            'is_branch_and_bound' => true,
        ];
    }
    
    private function encontrarVariavelNaoInteira(array $solucao)
    {
        for ($i = 1; $i <= $this->variaveisOriginais; $i++) {
            $var = 'x' . $i;
            if (isset($solucao[$var])) {
                if (abs($solucao[$var] - round($solucao[$var])) > 1e-9) {
                    return $i; 
                }
            }
        }
        return null;
    }
    
    private function adicionarRestricao(array $problema, int $variavelIndex, float $valor, string $sinal)
    {
        // Usar unserialize(serialize(...)) para uma cópia profunda e segura do array
        $novoProblema = unserialize(serialize($problema));

        // Determina o número de colunas (variáveis) no tableau atual
        $numCoeficientes = count($novoProblema[0]['coeficientes']);
        
        $novaRestricao = [
            'coeficientes' => array_fill(1, $numCoeficientes, 0.0),
            'sinal' => $sinal,
            'termo' => $valor,
            'tipoVariavel' => [] // Inicializa
        ];
        $novaRestricao['coeficientes'][$variavelIndex] = 1.0;
        
        $novaVarIndex = $numCoeficientes + 1;
        
        // Adiciona a nova coluna em todas as linhas existentes
        foreach ($novoProblema as &$linha) {
            $linha['coeficientes'][$novaVarIndex] = 0.0;
        }
        unset($linha);
        
        if ($sinal === '<=') {
            $novaRestricao['coeficientes'][$novaVarIndex] = 1.0; // Folga
            $novaRestricao['tipoVariavel']['folga'] = $novaVarIndex;
        } else { // '>='
            $novaRestricao['coeficientes'][$novaVarIndex] = -1.0; // Excesso
            $novaRestricao['tipoVariavel']['excesso'] = $novaVarIndex;
            
            // Adicionar variável artificial para restrições '>='
            $varArtificialIndex = $novaVarIndex + 1;
             foreach ($novoProblema as &$linha) {
                $linha['coeficientes'][$varArtificialIndex] = 0.0;
            }
            unset($linha);
            
            $novaRestricao['coeficientes'][$varArtificialIndex] = 1.0; // Artificial
            $novaRestricao['tipoVariavel']['artificial'] = $varArtificialIndex;

            // Adiciona BigM na função objetivo para a nova variável artificial
            $novoProblema[0]['coeficientes'][$varArtificialIndex] = ($this->tipoObjetivo === 'max') ? -app('bigM') : app('bigM');
        }
        
        array_push($novoProblema, $novaRestricao);
        
        return $novoProblema;
    }
}