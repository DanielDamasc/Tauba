<?php

namespace App\Services;

class SolverSimplexService
{
    private $tolerancia = 1e-9; // Precisão aumentada ligeiramente para comparações
    private $tipoObjetivo; // 'max' ou 'min'

    /**
     * Resolve um problema de programação linear usando o método Simplex.
     *
     * @param array $tabela O tableau simplex inicial.
     * É esperado que $tabela[0] seja a linha da função objetivo.
     * Cada linha é um array: ['coeficientes' => [], 'termo' => float]
     * @param string $tipoObjetivo 'max' para maximização ou 'min' para minimização.
     * @return array Um array contendo as iterações e a solução final.
     * @throws \InvalidArgumentException Se $tipoObjetivo for inválido.
     * @throws \Exception Se o problema for ilimitado ou ocorrerem outros problemas.
     */
    public function solverSimplex(array $tabela, string $tipoObjetivo)
    {
        $this->tipoObjetivo = strtolower($tipoObjetivo);

        if ($this->tipoObjetivo !== 'max' && $this->tipoObjetivo !== 'min') {
            throw new \InvalidArgumentException("Tipo de objetivo inválido. Deve ser 'max' ou 'min'.");
        }

        $iteracoes = [];
        $passo = 0;

        // Garante que todos os arrays de coeficientes sejam indexados numericamente e os valores sejam float
        foreach ($tabela as &$linha) {
            if (isset($linha['coeficientes']) && is_array($linha['coeficientes'])) {
                $linha['coeficientes'] = array_map('floatval', array_values($linha['coeficientes']));
            } else {
                $linha['coeficientes'] = [];
            }
            if (isset($linha['termo'])) {
                $linha['termo'] = floatval($linha['termo']);
            } else {
                $linha['termo'] = 0.0;
            }
        }
        unset($linha); // quebra a referência com o último elemento

        // Loop principal do simplex
        while ($this->deveContinuar($tabela[0]['coeficientes'])) {
            $passo++;

            $colunaPivo = $this->encontrarColunaPivo($tabela[0]['coeficientes']);

            if ($colunaPivo === -1) {
                $iteracoes[] = $this->formatarIteracao($tabela, null, null, $passo, "Nenhuma coluna pivô válida pôde ser selecionada (possivelmente ótimo ou erro de lógica).");
                 return [
                    'iteracoes' => $iteracoes,
                    'solucao' => $this->extrairSolucao($tabela),
                    'status' => 'otimo_ou_erro_coluna_pivo'
                ];
            }

            $linhaPivo = $this->encontrarLinhaPivo($tabela, $colunaPivo);

            if ($linhaPivo === null) {
                $iteracoes[] = $this->formatarIteracao($tabela, $colunaPivo, null, $passo, "Problema ilimitado. Não foi possível encontrar uma linha pivô.");
                return [
                    'iteracoes' => $iteracoes,
                    'solucao' => null,
                    'status' => 'ilimitado'
                ];
            }
            
            $elementoPivoOriginal = $tabela[$linhaPivo]['coeficientes'][$colunaPivo];
            $iteracoes[] = $this->formatarIteracao($tabela, $colunaPivo, $linhaPivo, $passo, null, $elementoPivoOriginal);

            $pivoValor = $tabela[$linhaPivo]['coeficientes'][$colunaPivo];
            if (abs($pivoValor) < $this->tolerancia) {
                 $iteracoes[] = $this->formatarIteracao($tabela, $colunaPivo, $linhaPivo, $passo, "Erro: Elemento pivô (" . $pivoValor . ") muito próximo de zero. Instabilidade numérica ou problema degenerado.");
                 return [
                    'iteracoes' => $iteracoes,
                    'solucao' => $this->extrairSolucao($tabela),
                    'status' => 'erro_pivo_zero'
                 ];
            }

            // --- Pivoteamento ---
            // Normaliza a linha pivô
            for ($j = 0; $j < count($tabela[$linhaPivo]['coeficientes']); $j++) {
                $tabela[$linhaPivo]['coeficientes'][$j] /= $pivoValor;
            }
            $tabela[$linhaPivo]['termo'] /= $pivoValor;
            $tabela[$linhaPivo]['coeficientes'][$colunaPivo] = 1.0;

            // Atualiza as outras linhas
            for ($i = 0; $i < count($tabela); $i++) {
                if ($i === $linhaPivo) continue;

                $fator = $tabela[$i]['coeficientes'][$colunaPivo];
                for ($j = 0; $j < count($tabela[$linhaPivo]['coeficientes']); $j++) {
                    $tabela[$i]['coeficientes'][$j] -= $fator * $tabela[$linhaPivo]['coeficientes'][$j];
                }
                $tabela[$i]['termo'] -= $fator * $tabela[$linhaPivo]['termo'];
                 if(isset($tabela[$i]['coeficientes'][$colunaPivo])) {
                    $tabela[$i]['coeficientes'][$colunaPivo] = 0.0;
                 }
            }
            
            foreach ($tabela as &$r) {
                foreach ($r['coeficientes'] as &$c) {
                    if (abs($c) < $this->tolerancia) $c = 0.0;
                }
                unset($c);
                if (abs($r['termo']) < $this->tolerancia) $r['termo'] = 0.0;
            }
            unset($r);
        }

        $iteracoes[] = $this->formatarIteracao($tabela, null, null, $passo + 1, "Solução ótima encontrada.");
        $solucao = $this->extrairSolucao($tabela);
        
        // --- INÍCIO DA MODIFICAÇÃO: Verificação e Cálculo de Múltiplas Soluções ---
        $status = 'otimo';
        $variaveisMultiplas = [];
        $solucaoAlternativa = [];

        // 1. Identificar colunas básicas
        $indicesColunasBasicas = [];
        $numCoefs = count($tabela[0]['coeficientes']);
        for ($j = 0; $j < $numCoefs; $j++) {
            $contagemDeUns = 0;
            $outrosNaoZero = false;
            for ($i = 1; $i < count($tabela); $i++) {
                if (abs($tabela[$i]['coeficientes'][$j] - 1.0) < $this->tolerancia) {
                    $contagemDeUns++;
                } elseif (abs($tabela[$i]['coeficientes'][$j]) > $this->tolerancia) {
                    $outrosNaoZero = true;
                    break;
                }
            }
            if (!$outrosNaoZero && $contagemDeUns === 1) {
                $indicesColunasBasicas[] = $j;
            }
        }

        // 2. Verificar variáveis não-básicas na linha Z
        for ($j = 0; $j < $numCoefs; $j++) {
            if (!in_array($j, $indicesColunasBasicas)) {
                if (isset($tabela[0]['coeficientes'][$j]) && abs($tabela[0]['coeficientes'][$j]) < $this->tolerancia) {
                    if ($this->encontrarLinhaPivo($tabela, $j) !== null) {
                       $variaveisMultiplas[] = 'x' . ($j + 1);
                    }
                }
            }
        }

        // 3. Se múltiplas soluções existem, calcular um ponto alternativo
        if (!empty($variaveisMultiplas)) {
            $status = 'multiplas_solucoes';

            // Escolhe a primeira variável elegível para entrar na base
            $primeiraVarParaPivotar = $variaveisMultiplas[0];
            $colunaPivoAlt = (int)substr($primeiraVarParaPivotar, 1) - 1;
            
            $linhaPivoAlt = $this->encontrarLinhaPivo($tabela, $colunaPivoAlt);
            
            if ($linhaPivoAlt !== null) {
                // Cria uma cópia do tableau para não alterar o original
                $tabelaAlternativa = $tabela;
                $pivoValor = $tabelaAlternativa[$linhaPivoAlt]['coeficientes'][$colunaPivoAlt];

                // Pivoteamento na tabela alternativa
                // Normaliza a linha pivô
                for ($j = 0; $j < count($tabelaAlternativa[$linhaPivoAlt]['coeficientes']); $j++) {
                    $tabelaAlternativa[$linhaPivoAlt]['coeficientes'][$j] /= $pivoValor;
                }
                $tabelaAlternativa[$linhaPivoAlt]['termo'] /= $pivoValor;

                // Atualiza as outras linhas
                for ($i = 0; $i < count($tabelaAlternativa); $i++) {
                    if ($i === $linhaPivoAlt) continue;
                    $fator = $tabelaAlternativa[$i]['coeficientes'][$colunaPivoAlt];
                    for ($j = 0; $j < count($tabelaAlternativa[$linhaPivoAlt]['coeficientes']); $j++) {
                        $tabelaAlternativa[$i]['coeficientes'][$j] -= $fator * $tabelaAlternativa[$linhaPivoAlt]['coeficientes'][$j];
                    }
                    $tabelaAlternativa[$i]['termo'] -= $fator * $tabelaAlternativa[$linhaPivoAlt]['termo'];
                }

                // Extrai a nova solução do tableau modificado
                $solucaoAlternativa = $this->extrairSolucao($tabelaAlternativa);
            }
        }
        // --- FIM DA MODIFICAÇÃO ---

        return [
            'iteracoes' => $iteracoes,
            'solucao' => $solucao,
            'status' => $status,
            'variaveisMultiplas' => $variaveisMultiplas,
            'solucaoAlternativa' => $solucaoAlternativa // Envia a solução alternativa para a view
        ];
    }

    /**
     * Verifica se o algoritmo Simplex deve continuar.
     */
    private function deveContinuar(array $coeficientesZ): bool
    {
        if ($this->tipoObjetivo === 'max') {
            foreach ($coeficientesZ as $value) {
                if ($value < -$this->tolerancia) return true;
            }
            return false;
        } else { // min
            foreach ($coeficientesZ as $value) {
                if ($value > $this->tolerancia) return true;
            }
            return false;
        }
    }

    /**
     * Encontra a coluna pivô.
     */
    private function encontrarColunaPivo(array $coeficientesZ): int
    {
        $colunaPivo = -1;
        $numCoefs = count($coeficientesZ);

        if ($this->tipoObjetivo === 'max') {
            $minVal = -$this->tolerancia;
            for ($j = 0; $j < $numCoefs; $j++) {
                if ($coeficientesZ[$j] < $minVal) {
                    $minVal = $coeficientesZ[$j];
                    $colunaPivo = $j;
                }
            }
        } else { // min
            $maxVal = $this->tolerancia;
            for ($j = 0; $j < $numCoefs; $j++) {
                if ($coeficientesZ[$j] > $maxVal) {
                    $maxVal = $coeficientesZ[$j];
                    $colunaPivo = $j;
                }
            }
        }
        return $colunaPivo;
    }

    /**
     * Encontra a linha pivô usando o teste da razão mínima não-negativa.
     */
    private function encontrarLinhaPivo(array $tabela, int $colunaPivo): ?int
    {
        $linhaPivo = null;
        $minRazaoNaoNegativa = PHP_FLOAT_MAX;
        $numLinhas = count($tabela);

        for ($i = 1; $i < $numLinhas; $i++) {
            if (!isset($tabela[$i]['coeficientes'][$colunaPivo])) {
                continue;
            }

            $coeficienteColunaPivo = $tabela[$i]['coeficientes'][$colunaPivo];
            $termoIndependente = $tabela[$i]['termo'];

            if ($coeficienteColunaPivo > $this->tolerancia) {
                if ($termoIndependente >= -$this->tolerancia) {
                    $razao = $termoIndependente / $coeficienteColunaPivo;
                    if ($razao >= -$this->tolerancia) {
                         if ($razao < $minRazaoNaoNegativa) {
                            $minRazaoNaoNegativa = $razao;
                            $linhaPivo = $i;
                        }
                    }
                }
            }
        }
        return $linhaPivo;
    }

    /**
     * Formata os dados de uma iteração para exibição.
     */
    private function formatarIteracao(array $tabela, ?int $colunaPivo, ?int $linhaPivo, int $passo, string $mensagem = null, ?float $elementoPivoOriginal = null): array
    {
        $dadosFormatados = [];
        $precisaoRound = 4;

        foreach ($tabela as $idx => $linha) {
            $coefsFormatados = array_map(fn($c) => round($c, $precisaoRound), $linha['coeficientes']);
            $termoFormatado = round($linha['termo'], $precisaoRound);
            
            $dadosFormatados[] = [
                'coeficientes' => $coefsFormatados,
                'termo' => $termoFormatado,
                'isLinhaPivo' => ($idx === $linhaPivo),
            ];
        }

        $iteracaoInfo = [
            'passo' => $passo,
            'tabela' => $dadosFormatados,
            'colunaPivo' => $colunaPivo,
            'linhaPivo' => $linhaPivo,
        ];

        if ($elementoPivoOriginal !== null && $linhaPivo !== null && $colunaPivo !== null) {
            $iteracaoInfo['elementoPivoValorOriginal'] = round($elementoPivoOriginal, $precisaoRound);
        }

        if ($mensagem) {
            $iteracaoInfo['mensagem'] = $mensagem;
        }

        return $iteracaoInfo;
    }

   /**
     * Extrai a solução final do tableau.
     */
    private function extrairSolucao(array $tabela): array
    {
        $solucao = [];
        if (empty($tabela) || !isset($tabela[0]['coeficientes']) || empty($tabela[0]['coeficientes'])) {
            $solucao['Z'] = 0.0;
            return $solucao;
        }

        $numTotalColunasCoeficientes = count($tabela[0]['coeficientes']);
        
        for ($j = 0; $j < $numTotalColunasCoeficientes; $j++) {
            $isColunaBasicaParaRestricao = false;
            $linhaDaBase = -1;
            $contagemDeUnsNaColuna = 0;
            $outrosValoresNaoZeroNaColuna = false;

            for ($i = 1; $i < count($tabela); $i++) {
                if (!isset($tabela[$i]['coeficientes'][$j])) continue;

                $valorCoef = $tabela[$i]['coeficientes'][$j];

                if (abs($valorCoef - 1.0) < $this->tolerancia) {
                    $contagemDeUnsNaColuna++;
                    $linhaDaBase = $i;
                } elseif (abs($valorCoef) > $this->tolerancia) {
                    $outrosValoresNaoZeroNaColuna = true;
                    break; 
                }
            }

            if ($contagemDeUnsNaColuna === 1 && !$outrosValoresNaoZeroNaColuna) {
                if (isset($tabela[0]['coeficientes'][$j]) && abs($tabela[0]['coeficientes'][$j]) < $this->tolerancia) {
                    $solucao['x' . ($j + 1)] = round($tabela[$linhaDaBase]['termo'], 4);
                } else {
                     if (!array_key_exists('x' . ($j + 1), $solucao)) {
                        $solucao['x' . ($j + 1)] = 0.0;
                    }
                }
            } else {
                 if (!array_key_exists('x' . ($j + 1), $solucao)) {
                    $solucao['x' . ($j + 1)] = 0.0;
                }
            }
        }
        
        $valorZ = round($tabela[0]['termo'], 4);
        
        $solucao['Z'] = $valorZ;

        return $solucao;
    }
}