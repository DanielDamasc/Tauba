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

        // 1. Identificar colunas básicas (LÓGICA CORRIGIDA)
        // Esta lógica robusta primeiro encontra a variável básica para cada linha de restrição.
        $indicesColunasBasicas = [];
        $numLinhas = count($tabela);
        $numCoefs = count($tabela[0]['coeficientes']);
        for ($i = 1; $i < $numLinhas; $i++) { // Itera por cada linha de restrição
            for ($j = 0; $j < $numCoefs; $j++) { // Procura a coluna básica para esta linha
                if (abs($tabela[$i]['coeficientes'][$j] - 1.0) < $this->tolerancia) {
                    $eColunaBasicaNestaPosicao = true;
                    for ($k = 0; $k < $numLinhas; $k++) {
                        if ($i === $k) continue;
                        if (abs($tabela[$k]['coeficientes'][$j]) > $this->tolerancia) {
                            $eColunaBasicaNestaPosicao = false;
                            break;
                        }
                    }
                    if ($eColunaBasicaNestaPosicao) {
                        $indicesColunasBasicas[] = $j;
                        break; // Vai para a próxima linha
                    }
                }
            }
        }
        
        // 2. Verificar variáveis não-básicas na linha Z (Este bloco agora funcionará corretamente)
        for ($j = 0; $j < $numCoefs; $j++) {
            if (!in_array($j, $indicesColunasBasicas)) {
                if (isset($tabela[0]['coeficientes'][$j]) && abs($tabela[0]['coeficientes'][$j]) < $this->tolerancia) {
                    if ($this->encontrarLinhaPivo($tabela, $j) !== null) {
                       $variaveisMultiplas[] = 'x' . ($j + 1);
                    }
                }
            }
        }

        // 3. Se múltiplas soluções existem, calcular um ponto alternativo (Este bloco não precisa de alteração)
       if (!empty($variaveisMultiplas)) {
            $status = 'multiplas_solucoes';

            // Abandona a ideia de pivoteamento. Vamos calcular o novo ponto manualmente.
            $linhaPivoAlt = $this->encontrarLinhaPivo($tabela, (int)substr($variaveisMultiplas[0], 1) - 1);
            
            if ($linhaPivoAlt !== null) {
                // Inicializa a solução alternativa como uma cópia da original.
                $solucaoAlternativa = $solucao;

                // A) Identifica a variável que vai ENTRAR na base e a que vai SAIR.
                $colunaEntrando = (int)substr($variaveisMultiplas[0], 1) - 1;
                $varEntrando = 'x' . ($colunaEntrando + 1);
                
                $colunaSaindo = -1;
                // A variável que sai é a que é básica na linha do pivô.
                for ($j = 0; $j < $numCoefs; $j++) {
                    // Verifica se a coluna $j é um vetor base (1 na linha do pivô, 0 nas outras)
                    if (abs($tabela[$linhaPivoAlt]['coeficientes'][$j] - 1.0) < $this->tolerancia) {
                        $eColunaBasica = true;
                        for ($i = 1; $i < $numLinhas; $i++) {
                            if ($i != $linhaPivoAlt && abs($tabela[$i]['coeficientes'][$j]) > $this->tolerancia) {
                                $eColunaBasica = false;
                                break;
                            }
                        }
                        if ($eColunaBasica) {
                            $colunaSaindo = $j;
                            break;
                        }
                    }
                }
                $varSaindo = ($colunaSaindo !== -1) ? 'x' . ($colunaSaindo + 1) : null;

                // B) Calcula o valor da variável que ENTRA (theta).
                $pivoValor = $tabela[$linhaPivoAlt]['coeficientes'][$colunaEntrando];
                $theta = $tabela[$linhaPivoAlt]['termo'] / $pivoValor;

                // C) Define os novos valores na solução alternativa.
                $solucaoAlternativa[$varEntrando] = $theta;
                if ($varSaindo) {
                    $solucaoAlternativa[$varSaindo] = 0;
                }

                // D) Atualiza os valores das OUTRAS variáveis que permaneceram na base.
                foreach ($solucaoAlternativa as $var => &$valor) {
                    if ($var == 'Z' || $var == $varEntrando || $var == $varSaindo || $valor == 0) continue;
                    
                    // Encontra a linha da variável básica atual
                    $linhaVarAtual = -1;
                    $colVarAtual = (int)substr($var, 1) - 1;
                    for ($i = 1; $i < $numLinhas; $i++) {
                        if (abs($tabela[$i]['coeficientes'][$colVarAtual] - 1.0) < $this->tolerancia) {
                            $linhaVarAtual = $i;
                            break;
                        }
                    }
                    
                    if ($linhaVarAtual != -1) {
                        $coeficienteCruzado = $tabela[$linhaVarAtual]['coeficientes'][$colunaEntrando];
                        $valor -= $coeficienteCruzado * $theta; // Fórmula: Novo Valor = Velho Valor - a_ik * theta
                    }
                }
                unset($valor); // Quebra a referência

                // E) Arredonda todos os valores para uma exibição limpa.
                foreach ($solucaoAlternativa as $key => $val) {
                    $solucaoAlternativa[$key] = round($val, 4);
                }
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
        if (empty($tabela) || !isset($tabela[0]['coeficientes'])) {
            return ['Z' => 0.0];
        }

        $numLinhas = count($tabela);
        $numCoefs = count($tabela[0]['coeficientes']);
        
        // Array para rastrear se uma linha de restrição já foi usada para uma variável básica.
        $linhaJaUsada = array_fill(1, $numLinhas - 1, false);

        // 1. Inicializa todas as variáveis como não-básicas (valor 0)
        for ($j = 0; $j < $numCoefs; $j++) {
            $solucao['x' . ($j + 1)] = 0.0;
        }

        // 2. Itera por cada COLUNA para encontrar as variáveis básicas
        for ($j = 0; $j < $numCoefs; $j++) {
            $linhaDoPivo = -1;
            $isColunaBasica = true;

            // Verifica se a coluna tem um único '1' e o resto '0's (nas restrições)
            for ($i = 1; $i < $numLinhas; $i++) {
                $coef = $tabela[$i]['coeficientes'][$j] ?? 0.0;
                if (abs($coef - 1.0) < $this->tolerancia) {
                    if ($linhaDoPivo !== -1) { // Já encontrou um '1' nesta coluna
                        $isColunaBasica = false;
                        break;
                    }
                    $linhaDoPivo = $i;
                } elseif (abs($coef) > $this->tolerancia) { // Encontrou outro valor que não é 0 nem 1
                    $isColunaBasica = false;
                    break;
                }
            }

            // 3. Se a coluna é básica E a sua linha de pivô ainda não foi usada
            if ($isColunaBasica && $linhaDoPivo !== -1 && !$linhaJaUsada[$linhaDoPivo]) {
                // Atribui o valor e MARCA a linha como usada
                $solucao['x' . ($j + 1)] = round($tabela[$linhaDoPivo]['termo'], 4);
                $linhaJaUsada[$linhaDoPivo] = true;
            }
        }
        
        // 4. O valor de Z é sempre o termo na linha 0
        $solucao['Z'] = round($tabela[0]['termo'], 4);

        return $solucao;
    }
}