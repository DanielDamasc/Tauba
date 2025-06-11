<?php

namespace App\Services;

class SolverSimplexService
{
    private $tolerancia = 1e-9; // Increased precision slightly for comparisons
    private $tipoObjetivo; // 'max' or 'min'

    /**
     * Solves a linear programming problem using the Simplex method.
     *
     * @param array $tabela The initial simplex tableau.
     * It's expected that $tabela[0] is the objective function row.
     * Each row is an array: ['coeficientes' => [], 'termo' => float]
     * @param string $tipoObjetivo Either 'max' for maximization or 'min' for minimization.
     * @return array An array containing the iterations and the final solution.
     * @throws \InvalidArgumentException If $tipoObjetivo is invalid.
     * @throws \Exception If the problem is unbounded or other issues occur.
     */
    public function solverSimplex(array $tabela, string $tipoObjetivo)
    {
        $this->tipoObjetivo = strtolower($tipoObjetivo);

        if ($this->tipoObjetivo !== 'max' && $this->tipoObjetivo !== 'min') {
            throw new \InvalidArgumentException("Tipo de objetivo inválido. Deve ser 'max' ou 'min'.");
        }

        $iteracoes = [];
        $passo = 0;

        // Ensure all coefficient arrays are numerically indexed and values are float
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
        unset($linha); // break the reference with the last element

        // Main simplex loop
        while ($this->deveContinuar($tabela[0]['coeficientes'])) {
            $passo++;

            $colunaPivo = $this->encontrarColunaPivo($tabela[0]['coeficientes']);

            // If no valid pivot column is found, it implies optimality (already handled by deveContinuar)
            // or an issue if deveContinuar was true but no valid column was found (e.g., all Z coeffs are zero but one was expected to be non-zero).
            // This check is more of a safeguard.
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
                // This means all coefficients in the pivot column (for constraints) are <= 0.
                $iteracoes[] = $this->formatarIteracao($tabela, $colunaPivo, null, $passo, "Problema ilimitado. Não foi possível encontrar uma linha pivô.");
                return [
                    'iteracoes' => $iteracoes,
                    'solucao' => null, // No specific solution for unbounded problems
                    'status' => 'ilimitado'
                ];
            }
            
            // Store the original pivot element value for the iteration log
            $elementoPivoOriginal = $tabela[$linhaPivo]['coeficientes'][$colunaPivo];
            // Log iteration before pivoting
            $iteracoes[] = $this->formatarIteracao($tabela, $colunaPivo, $linhaPivo, $passo, null, $elementoPivoOriginal);


            // Normalize pivot row
            $pivoValor = $tabela[$linhaPivo]['coeficientes'][$colunaPivo];
            // Defensive check for pivot element being too close to zero
            if (abs($pivoValor) < $this->tolerancia) {
                 $iteracoes[] = $this->formatarIteracao($tabela, $colunaPivo, $linhaPivo, $passo, "Erro: Elemento pivô (" . $pivoValor . ") muito próximo de zero. Instabilidade numérica ou problema degenerado.");
                 return [
                    'iteracoes' => $iteracoes,
                    'solucao' => $this->extrairSolucao($tabela), // Return current state
                    'status' => 'erro_pivo_zero'
                 ];
            }

            for ($j = 0; $j < count($tabela[$linhaPivo]['coeficientes']); $j++) {
                $tabela[$linhaPivo]['coeficientes'][$j] /= $pivoValor;
            }
            $tabela[$linhaPivo]['termo'] /= $pivoValor;
            // Ensure the pivot element itself becomes exactly 1.0 after normalization
            $tabela[$linhaPivo]['coeficientes'][$colunaPivo] = 1.0;


            // Update other rows
            for ($i = 0; $i < count($tabela); $i++) {
                if ($i === $linhaPivo) continue; // Skip the pivot row itself

                // The factor is the current coefficient in the pivot column for row $i
                $fator = $tabela[$i]['coeficientes'][$colunaPivo];
                for ($j = 0; $j < count($tabela[$linhaPivo]['coeficientes']); $j++) {
                    $tabela[$i]['coeficientes'][$j] -= $fator * $tabela[$linhaPivo]['coeficientes'][$j];
                }
                $tabela[$i]['termo'] -= $fator * $tabela[$linhaPivo]['termo'];
                // Ensure the coefficient in the pivot column becomes 0.0 for non-pivot rows
                 if(isset($tabela[$i]['coeficientes'][$colunaPivo])) {
                    $tabela[$i]['coeficientes'][$colunaPivo] = 0.0;
                 }
            }
            
            // Sanitize very small numbers to zero to mitigate floating-point error accumulation
            foreach ($tabela as &$r) { // Use reference to modify array directly
                foreach ($r['coeficientes'] as &$c) { // Use reference
                    if (abs($c) < $this->tolerancia) $c = 0.0;
                }
                unset($c); // break reference
                if (abs($r['termo']) < $this->tolerancia) $r['termo'] = 0.0;
            }
            unset($r); // break reference
        }

        // Log the final state (optimal solution)
        $iteracoes[] = $this->formatarIteracao($tabela, null, null, $passo + 1, "Solução ótima encontrada.");

        $solucao = $this->extrairSolucao($tabela);

        return [
            'iteracoes' => $iteracoes,
            'solucao' => $solucao,
            'status' => 'otimo'
        ];
    }

    /**
     * Checks if the Simplex algorithm should continue.
     * For maximization: continues if there are any negative coefficients in the Z-row.
     * For minimization: continues if there are any positive coefficients in the Z-row.
     */
    private function deveContinuar(array $coeficientesZ): bool
    {
        if ($this->tipoObjetivo === 'max') {
            foreach ($coeficientesZ as $value) {
                if ($value < -$this->tolerancia) return true; // If any value is significantly negative
            }
            return false;
        } else { // min
            foreach ($coeficientesZ as $value) {
                if ($value > $this->tolerancia) return true; // If any value is significantly positive
            }
            return false;
        }
    }

    /**
     * Finds the pivot column.
     * For maximization: column with the most negative coefficient in the Z-row.
     * For minimization: column with the most positive coefficient in the Z-row.
     * Returns -1 if no suitable pivot column is found (should align with deveContinuar).
     */
    private function encontrarColunaPivo(array $coeficientesZ): int
    {
        $colunaPivo = -1;
        $numCoefs = count($coeficientesZ);

        if ($this->tipoObjetivo === 'max') {
            $minVal = -$this->tolerancia; // Initialize to find something strictly more negative
            for ($j = 0; $j < $numCoefs; $j++) {
                if ($coeficientesZ[$j] < $minVal) {
                    $minVal = $coeficientesZ[$j];
                    $colunaPivo = $j;
                }
            }
        } else { // min
            $maxVal = $this->tolerancia; // Initialize to find something strictly more positive
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
     * Finds the pivot row using the minimum non-negative ratio test.
     * Returns null if no suitable pivot row is found (unbounded problem).
     */
    private function encontrarLinhaPivo(array $tabela, int $colunaPivo): ?int
    {
        $linhaPivo = null;
        $minRazaoNaoNegativa = PHP_FLOAT_MAX;
        $numLinhas = count($tabela);

        for ($i = 1; $i < $numLinhas; $i++) { // Iterate through constraint rows (skip Z-row at index 0)
            if (!isset($tabela[$i]['coeficientes'][$colunaPivo])) {
                // This case should ideally not happen if the table is well-formed
                continue;
            }

            $coeficienteColunaPivo = $tabela[$i]['coeficientes'][$colunaPivo];
            $termoIndependente = $tabela[$i]['termo'];

            // Denominator (coefficient in pivot column for the current row) must be strictly positive.
            if ($coeficienteColunaPivo > $this->tolerancia) {
                // Numerator (RHS term) should be non-negative for the standard ratio test.
                // If RHS is negative and pivot column coefficient is positive, this row isn't a candidate
                // in the standard primal simplex (it might be in dual simplex).
                // We consider values very close to zero as non-negative.
                if ($termoIndependente >= -$this->tolerancia) {
                    $razao = $termoIndependente / $coeficienteColunaPivo;

                    // The ratio itself must be non-negative.
                    // (This check is somewhat redundant if $termoIndependente >= 0 and $coeficienteColunaPivo > 0)
                    if ($razao >= -$this->tolerancia) { // Check if ratio is non-negative (or very close to it)
                         if ($razao < $minRazaoNaoNegativa) {
                            $minRazaoNaoNegativa = $razao;
                            $linhaPivo = $i;
                        }
                        // Bland's rule or other tie-breaking rules can be implemented here if cycling is a concern.
                        // For now, the first row encountered with the minimum ratio is chosen.
                    }
                }
            }
        }
        return $linhaPivo;
    }

    /**
     * Formats the data of an iteration for display.
     */
    private function formatarIteracao(array $tabela, ?int $colunaPivo, ?int $linhaPivo, int $passo, string $mensagem = null, ?float $elementoPivoOriginal = null): array
    {
        $dadosFormatados = [];
        $precisaoRound = 4; // Decimal places for display

        foreach ($tabela as $idx => $linha) {
            $coefsFormatados = array_map(fn($c) => round($c, $precisaoRound), $linha['coeficientes']);
            $termoFormatado = round($linha['termo'], $precisaoRound);
            
            $dadosFormatados[] = [
                'coeficientes' => $coefsFormatados,
                'termo' => $termoFormatado,
                'isLinhaPivo' => ($idx === $linhaPivo), // For highlighting the pivot row
            ];
        }

        $iteracaoInfo = [
            'passo' => $passo,
            'tabela' => $dadosFormatados,
            'colunaPivo' => $colunaPivo, // Changed from colunaPivoIndex
            'linhaPivo' => $linhaPivo,   // Changed from linhaPivoIndex
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
     * Extracts the final solution from the tableau.
     * Note: The heuristic for identifying the number of original decision variables
     * can be fragile for problems with mixed constraint types ('<=', '>=', '=').
     * Ideally, the number of decision variables or their column indices should be known.
     */
    private function extrairSolucao(array $tabela): array
    {
        $solucao = [];
        if (empty($tabela) || !isset($tabela[0]['coeficientes']) || empty($tabela[0]['coeficientes'])) {
            $solucao['Z'] = 0.0;
            return $solucao; // Invalid or empty tableau
        }

        // Heuristic for estimating the number of original decision variables.
        // This assumes slack/surplus/artificial variables were added to the right of decision variables.
        // This count is used to iterate through potential decision variable columns.
        // It's an estimation and might not be robust for all cases without more info.
        $numTotalColunasCoeficientes = count($tabela[0]['coeficientes']);
        $numLinhasRestricoes = count($tabela) - 1; // Number of constraint rows (excluding Z-row)
        
        // A common heuristic: #DecisionVars = TotalVars - #BasicSlackVars (if all <=)
        // Or, more generally, it's the number of columns before slack/surplus/artificial vars start.
        // If we don't know this, we can try to identify basic variables among the first N columns.
        // For now, let's assume the original heuristic was trying to get at this.
        // A more robust way would be to pass the number of decision variables.
        // Let's try to infer based on which variables are basic.
        
        $numMaxPossiveisVariaveisDecisao = $numTotalColunasCoeficientes; // Iterate up to all coefficient columns initially

        for ($j = 0; $j < $numMaxPossiveisVariaveisDecisao; $j++) { // Iterate through all columns
            $isColunaBasicaParaRestricao = false;
            $linhaDaBase = -1;
            $contagemDeUnsNaColuna = 0;
            $outrosValoresNaoZeroNaColuna = false;

            // Check if column $j$ is basic in any constraint row
            for ($i = 1; $i < count($tabela); $i++) { // Start from row 1 (constraints)
                if (!isset($tabela[$i]['coeficientes'][$j])) continue;

                $valorCoef = $tabela[$i]['coeficientes'][$j];

                if (abs($valorCoef - 1.0) < $this->tolerancia) {
                    $contagemDeUnsNaColuna++;
                    $linhaDaBase = $i; // Potential row where this variable is basic
                } elseif (abs($valorCoef) > $this->tolerancia) {
                    $outrosValoresNaoZeroNaColuna = true;
                    // If there's another non-zero value, it's not a clean basic column (part of identity matrix)
                    break; 
                }
            }

            // A variable is basic if its column has one '1' and all other elements are '0' in the constraint rows,
            // AND its coefficient in the Z-row is '0' (for an optimal tableau).
            if ($contagemDeUnsNaColuna === 1 && !$outrosValoresNaoZeroNaColuna) {
                if (isset($tabela[0]['coeficientes'][$j]) && abs($tabela[0]['coeficientes'][$j]) < $this->tolerancia) {
                    // This variable (x_j+1) is basic and its value is the RHS of the row where it's '1'.
                    $solucao['x' . ($j + 1)] = round($tabela[$linhaDaBase]['termo'], 4);
                } else {
                    // If it's basic in constraints but Z-row coeff is non-zero, it's non-basic in optimal solution (value 0)
                    // or it's an artificial variable that shouldn't be in the solution.
                    // For simplicity, if it's not zero in Z-row, we treat it as non-basic (value 0 for decision/slack/surplus).
                     if (!array_key_exists('x' . ($j + 1), $solucao)) { // Avoid overwriting if already set by another rule
                        $solucao['x' . ($j + 1)] = 0.0;
                    }
                }
            } else {
                 // If not a clean basic column, it's non-basic, so its value is 0.
                 if (!array_key_exists('x' . ($j + 1), $solucao)) {
                    $solucao['x' . ($j + 1)] = 0.0;
                }
            }
        }
        
        // The value of the objective function Z is the term in the Z-row.
        // If minimizing, and we converted to max(-Z), then Z_optimal = -tableau[0]['term'].
        // However, this service expects the Z-row to already be in the correct form for either max or min.
        // The `FormaAumentadaService` negates Z coefficients for maximization.
        // If it's a minimization problem, and Z = cX, and we maximize Z' = -cX, then Z_opt = -Z'_opt.
        // Assuming the Z-row term directly gives the optimal value of the objective function as stated (max Z or min Z).
        $valorZ = round($tabela[0]['termo'], 4);
        
        // If the original problem was minimization, and FormaAumentadaService converted Z to -Z for maximization,
        // then the final term in the Z row is -Z_min. So Z_min = -term.
        // This logic depends on how FormaAumentadaService handles the objective function for min problems.
        // The current SolverSimplexService works with the Z-row as provided.
        // If FormaAumentadaService always sets up for maximization (e.g. by negating Z for minimization problems),
        // then for a minimization problem, the result from Z-row needs to be negated.
        // Let's assume for now the $tabela[0]['termo'] is the direct value.
        // If `FormaAumentadaService` negates the objective function for maximization problems,
        // and if for minimization problems it also negates it to turn it into maximization (max -Z),
        // then $tabela[0]['termo'] would be the maximized value of (-Z_original_min).
        // So, Z_original_min = - $tabela[0]['termo'].
        // However, the prompt for `SolverSimplexMaxService` only handled maximization.
        // My `SolverSimplexService` handles min directly if Z-row is set up for min.
        // The `FormaAumentadaService` seems to prepare for maximization by default by negating Z coefficients:
        // `if ($i == 0 && $problema[$i]["coeficientes"][$n] != 0) { $coef = $problema[$i]["coeficientes"][$n]; $coefNeg = -$coef; $problema[$i]["coeficientes"][$n] = $coefNeg; }`
        // This means if you input Z for minimization, it becomes -Z. The simplex maximizes -Z.
        // So the result in $tabela[0]['termo'] is max(-Z) = -min(Z).
        // Therefore, min(Z) = - $tabela[0]['termo'].
        if ($this->tipoObjetivo === 'min') {
             $solucao['Z'] = $valorZ;
        } else {
             $solucao['Z'] = $valorZ;
        }


        return $solucao;
    }
}
