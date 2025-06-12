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

            // Se nenhuma coluna pivô válida for encontrada, isso implica otimalidade (já tratada por deveContinuar)
            // ou um problema se deveContinuar for verdadeiro mas nenhuma coluna válida foi encontrada (ex: todos os coeficientes de Z são zero, mas esperava-se que um não fosse).
            // Esta verificação é mais uma salvaguarda.
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
                // Isso significa que todos os coeficientes na coluna pivô (para as restrições) são <= 0.
                $iteracoes[] = $this->formatarIteracao($tabela, $colunaPivo, null, $passo, "Problema ilimitado. Não foi possível encontrar uma linha pivô.");
                return [
                    'iteracoes' => $iteracoes,
                    'solucao' => null, // Nenhuma solução específica para problemas ilimitados
                    'status' => 'ilimitado'
                ];
            }
            
            // Armazena o valor do elemento pivô original para o log da iteração
            $elementoPivoOriginal = $tabela[$linhaPivo]['coeficientes'][$colunaPivo];
            // Registra a iteração antes de pivotar
            $iteracoes[] = $this->formatarIteracao($tabela, $colunaPivo, $linhaPivo, $passo, null, $elementoPivoOriginal);


            // Normaliza a linha pivô
            $pivoValor = $tabela[$linhaPivo]['coeficientes'][$colunaPivo];
            // Verificação defensiva para o elemento pivô estar muito próximo de zero
            if (abs($pivoValor) < $this->tolerancia) {
                 $iteracoes[] = $this->formatarIteracao($tabela, $colunaPivo, $linhaPivo, $passo, "Erro: Elemento pivô (" . $pivoValor . ") muito próximo de zero. Instabilidade numérica ou problema degenerado.");
                 return [
                    'iteracoes' => $iteracoes,
                    'solucao' => $this->extrairSolucao($tabela), // Retorna o estado atual
                    'status' => 'erro_pivo_zero'
                 ];
            }

            for ($j = 0; $j < count($tabela[$linhaPivo]['coeficientes']); $j++) {
                $tabela[$linhaPivo]['coeficientes'][$j] /= $pivoValor;
            }
            $tabela[$linhaPivo]['termo'] /= $pivoValor;
            // Garante que o próprio elemento pivô se torne exatamente 1.0 após a normalização
            $tabela[$linhaPivo]['coeficientes'][$colunaPivo] = 1.0;


            // Atualiza as outras linhas
            for ($i = 0; $i < count($tabela); $i++) {
                if ($i === $linhaPivo) continue; // Pula a própria linha pivô

                // O fator é o coeficiente atual na coluna pivô para a linha $i
                $fator = $tabela[$i]['coeficientes'][$colunaPivo];
                for ($j = 0; $j < count($tabela[$linhaPivo]['coeficientes']); $j++) {
                    $tabela[$i]['coeficientes'][$j] -= $fator * $tabela[$linhaPivo]['coeficientes'][$j];
                }
                $tabela[$i]['termo'] -= $fator * $tabela[$linhaPivo]['termo'];
                // Garante que o coeficiente na coluna pivô se torne 0.0 para as linhas não-pivô
                 if(isset($tabela[$i]['coeficientes'][$colunaPivo])) {
                    $tabela[$i]['coeficientes'][$colunaPivo] = 0.0;
                 }
            }
            
            // Limpa números muito pequenos para zero para mitigar o acúmulo de erros de ponto flutuante
            foreach ($tabela as &$r) { // Usa referência para modificar o array diretamente
                foreach ($r['coeficientes'] as &$c) { // Usa referência
                    if (abs($c) < $this->tolerancia) $c = 0.0;
                }
                unset($c); // quebra a referência
                if (abs($r['termo']) < $this->tolerancia) $r['termo'] = 0.0;
            }
            unset($r); // quebra a referência
        }

        // Registra o estado final (solução ótima)
        $iteracoes[] = $this->formatarIteracao($tabela, null, null, $passo + 1, "Solução ótima encontrada.");

        $solucao = $this->extrairSolucao($tabela);

        return [
            'iteracoes' => $iteracoes,
            'solucao' => $solucao,
            'status' => 'otimo'
        ];
    }

    /**
     * Verifica se o algoritmo Simplex deve continuar.
     * Para maximização: continua se houver coeficientes negativos na linha Z.
     * Para minimização: continua se houver coeficientes positivos na linha Z.
     */
    private function deveContinuar(array $coeficientesZ): bool
    {
        if ($this->tipoObjetivo === 'max') {
            foreach ($coeficientesZ as $value) {
                if ($value < -$this->tolerancia) return true; // Se qualquer valor for significativamente negativo
            }
            return false;
        } else { // min
            foreach ($coeficientesZ as $value) {
                if ($value > $this->tolerancia) return true; // Se qualquer valor for significativamente positivo
            }
            return false;
        }
    }

    /**
     * Encontra a coluna pivô.
     * Para maximização: coluna com o coeficiente mais negativo na linha Z.
     * Para minimização: coluna com o coeficiente mais positivo na linha Z.
     * Retorna -1 se nenhuma coluna pivô adequada for encontrada (deve estar alinhado com deveContinuar).
     */
    private function encontrarColunaPivo(array $coeficientesZ): int
    {
        $colunaPivo = -1;
        $numCoefs = count($coeficientesZ);

        if ($this->tipoObjetivo === 'max') {
            $minVal = -$this->tolerancia; // Inicializa para encontrar algo estritamente mais negativo
            for ($j = 0; $j < $numCoefs; $j++) {
                if ($coeficientesZ[$j] < $minVal) {
                    $minVal = $coeficientesZ[$j];
                    $colunaPivo = $j;
                }
            }
        } else { // min
            $maxVal = $this->tolerancia; // Inicializa para encontrar algo estritamente mais positivo
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
     * Retorna null se nenhuma linha pivô adequada for encontrada (problema ilimitado).
     */
    private function encontrarLinhaPivo(array $tabela, int $colunaPivo): ?int
    {
        $linhaPivo = null;
        $minRazaoNaoNegativa = PHP_FLOAT_MAX;
        $numLinhas = count($tabela);

        for ($i = 1; $i < $numLinhas; $i++) { // Itera através das linhas de restrição (pula a linha Z no índice 0)
            if (!isset($tabela[$i]['coeficientes'][$colunaPivo])) {
                // Este caso idealmente não deveria acontecer se a tabela estiver bem formada
                continue;
            }

            $coeficienteColunaPivo = $tabela[$i]['coeficientes'][$colunaPivo];
            $termoIndependente = $tabela[$i]['termo'];

            // O denominador (coeficiente na coluna pivô para a linha atual) deve ser estritamente positivo.
            if ($coeficienteColunaPivo > $this->tolerancia) {
                // O numerador (termo do lado direito) deve ser não-negativo para o teste da razão padrão.
                // Se o lado direito for negativo e o coeficiente da coluna pivô for positivo, esta linha não é uma candidata
                // no simplex primal padrão (poderia ser no simplex dual).
                // Consideramos valores muito próximos de zero como não-negativos.
                if ($termoIndependente >= -$this->tolerancia) {
                    $razao = $termoIndependente / $coeficienteColunaPivo;

                    // A própria razão deve ser não-negativa.
                    // (Esta verificação é um tanto redundante se $termoIndependente >= 0 e $coeficienteColunaPivo > 0)
                    if ($razao >= -$this->tolerancia) { // Verifica se a razão é não-negativa (ou muito próxima disso)
                         if ($razao < $minRazaoNaoNegativa) {
                            $minRazaoNaoNegativa = $razao;
                            $linhaPivo = $i;
                        }
                        // A regra de Bland ou outras regras de desempate podem ser implementadas aqui se a ciclagem for uma preocupação.
                        // Por enquanto, a primeira linha encontrada com a razão mínima é escolhida.
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
        $precisaoRound = 4; // Casas decimais para exibição

        foreach ($tabela as $idx => $linha) {
            $coefsFormatados = array_map(fn($c) => round($c, $precisaoRound), $linha['coeficientes']);
            $termoFormatado = round($linha['termo'], $precisaoRound);
            
            $dadosFormatados[] = [
                'coeficientes' => $coefsFormatados,
                'termo' => $termoFormatado,
                'isLinhaPivo' => ($idx === $linhaPivo), // Para destacar a linha pivô
            ];
        }

        $iteracaoInfo = [
            'passo' => $passo,
            'tabela' => $dadosFormatados,
            'colunaPivo' => $colunaPivo, // Alterado de colunaPivoIndex
            'linhaPivo' => $linhaPivo,   // Alterado de linhaPivoIndex
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
     * Nota: A heurística para identificar o número de variáveis de decisão originais
     * pode ser frágil para problemas com tipos de restrição mistos ('<=', '>=', '=').
     * Idealmente, o número de variáveis de decisão ou seus índices de coluna deveriam ser conhecidos.
     */
    private function extrairSolucao(array $tabela): array
    {
        $solucao = [];
        if (empty($tabela) || !isset($tabela[0]['coeficientes']) || empty($tabela[0]['coeficientes'])) {
            $solucao['Z'] = 0.0;
            return $solucao; // Tableau inválido ou vazio
        }

        // Heurística para estimar o número de variáveis de decisão originais.
        // Isso assume que variáveis de folga/excesso/artificiais foram adicionadas à direita das variáveis de decisão.
        // Essa contagem é usada para iterar através das colunas de variáveis de decisão potenciais.
        // É uma estimativa e pode não ser robusta para todos os casos sem mais informações.
        $numTotalColunasCoeficientes = count($tabela[0]['coeficientes']);
        $numLinhasRestricoes = count($tabela) - 1; // Número de linhas de restrição (excluindo a linha Z)
        
        // Uma heurística comum: #VariaveisDeDecisao = TotalDeVariaveis - #VariaveisDeFolgaBasicas (se todas <=)
        // Ou, de forma mais geral, é o número de colunas antes do início das variáveis de folga/excesso/artificiais.
        // Se não soubermos isso, podemos tentar identificar variáveis básicas entre as primeiras N colunas.
        // Por enquanto, vamos assumir que a heurística original estava tentando chegar a isso.
        // Uma maneira mais robusta seria passar o número de variáveis de decisão.
        // Vamos tentar inferir com base em quais variáveis são básicas.
        
        $numMaxPossiveisVariaveisDecisao = $numTotalColunasCoeficientes; // Itera inicialmente até todas as colunas de coeficientes

        for ($j = 0; $j < $numMaxPossiveisVariaveisDecisao; $j++) { // Itera através de todas as colunas
            $isColunaBasicaParaRestricao = false;
            $linhaDaBase = -1;
            $contagemDeUnsNaColuna = 0;
            $outrosValoresNaoZeroNaColuna = false;

            // Verifica se a coluna $j$ é básica em alguma linha de restrição
            for ($i = 1; $i < count($tabela); $i++) { // Começa da linha 1 (restrições)
                if (!isset($tabela[$i]['coeficientes'][$j])) continue;

                $valorCoef = $tabela[$i]['coeficientes'][$j];

                if (abs($valorCoef - 1.0) < $this->tolerancia) {
                    $contagemDeUnsNaColuna++;
                    $linhaDaBase = $i; // Linha potencial onde esta variável é básica
                } elseif (abs($valorCoef) > $this->tolerancia) {
                    $outrosValoresNaoZeroNaColuna = true;
                    // Se houver outro valor não-zero, não é uma coluna básica limpa (parte da matriz identidade)
                    break; 
                }
            }

            // Uma variável é básica se sua coluna tem um '1' e todos os outros elementos são '0' nas linhas de restrição,
            // E seu coeficiente na linha Z é '0' (para um tableau ótimo).
            if ($contagemDeUnsNaColuna === 1 && !$outrosValoresNaoZeroNaColuna) {
                if (isset($tabela[0]['coeficientes'][$j]) && abs($tabela[0]['coeficientes'][$j]) < $this->tolerancia) {
                    // Esta variável (x_j+1) é básica e seu valor é o lado direito da linha onde ela é '1'.
                    $solucao['x' . ($j + 1)] = round($tabela[$linhaDaBase]['termo'], 4);
                } else {
                    // Se for básica nas restrições mas o coeficiente na linha Z não for zero, ela é não-básica na solução ótima (valor 0)
                    // ou é uma variável artificial que não deveria estar na solução.
                    // Por simplicidade, se não for zero na linha Z, a tratamos como não-básica (valor 0 para decisão/folga/excesso).
                     if (!array_key_exists('x' . ($j + 1), $solucao)) { // Evita sobrescrever se já foi definido por outra regra
                        $solucao['x' . ($j + 1)] = 0.0;
                    }
                }
            } else {
                 // Se não for uma coluna básica limpa, é não-básica, então seu valor é 0.
                 if (!array_key_exists('x' . ($j + 1), $solucao)) {
                    $solucao['x' . ($j + 1)] = 0.0;
                }
            }
        }
        
        $valorZ = round($tabela[0]['termo'], 4);
        
        if ($this->tipoObjetivo === 'min') {
             $solucao['Z'] = $valorZ;
        } else {
             $solucao['Z'] = $valorZ;
        }


        return $solucao;
    }
}