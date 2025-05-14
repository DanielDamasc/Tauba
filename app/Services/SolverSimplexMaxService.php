<?php

namespace App\Services;

class SolverSimplexMaxService
{
    private $tolerancia = 1e-6;
    public function solverSimplex($tabela)
    {
        $iteracoes = [];
        $passo = 0;
        $maxColunas = max(array_map(fn($linha) => count($linha['coeficientes']), $tabela));
        foreach ($tabela as &$linha) {
            $linha['coeficientes'] = array_values($linha['coeficientes']);
        }
        unset($linha);


        // Enquanto houver coeficientes negativos na linha Z (exceto RHS)
        while ($this->existeNegativo($tabela[0]['coeficientes'])) {
            $passo++;

            // Encontrar coluna pivô (mais negativo na linha Z)
            $colunaPivo = $this->encontrarColunaPivo($tabela[0]['coeficientes']);

            // Encontrar linha pivô (menor razão RHS/coeficiente positivo)
            $linhaPivo = $this->encontrarLinhaPivo($tabela, $colunaPivo);

            if ($linhaPivo === null) {
                throw new \Exception("Problema ilimitado.");
            }

            // Registrar estado atual da tabela antes do pivô
            $iteracoes[] = $this->formatarIteracao($tabela, $colunaPivo, $linhaPivo, $passo);

            // Normalizar linha pivô
            $pivoValor = $tabela[$linhaPivo]['coeficientes'][$colunaPivo];
            foreach ($tabela[$linhaPivo]['coeficientes'] as $key => $value) {
                $tabela[$linhaPivo]['coeficientes'][$key] /= $pivoValor;
            }
            $tabela[$linhaPivo]['termo'] /= $pivoValor;

            // Atualizar outras linhas
            foreach ($tabela as $i => $linha) {
                if ($i === $linhaPivo) continue;

                $fator = $linha['coeficientes'][$colunaPivo] ?? 0; // Usar 0 se não existir
                foreach ($tabela[$linhaPivo]['coeficientes'] as $j => $pivoCoef) {
                    // Inicializar índice se não existir
                    if (!isset($tabela[$i]['coeficientes'][$j])) {
                        $tabela[$i]['coeficientes'][$j] = 0;
                    }
                    $tabela[$i]['coeficientes'][$j] -= $fator * $pivoCoef;
                }
                $tabela[$i]['termo'] -= $fator * $tabela[$linhaPivo]['termo'];
            }
        }

        // Adicionar última iteração
        $iteracoes[] = $this->formatarIteracao($tabela, null, null, $passo + 1);

        // Extrair solução final
        $solucao = $this->extrairSolucao($tabela);

        return [
            'iteracoes' => $iteracoes,
            'solucao' => $solucao
        ];
    }

    private function existeNegativo($coeficientes)
    {
        foreach ($coeficientes as $value) {
            if ($value < -$this->tolerancia) return true;
        }
        return false;
    }

    private function encontrarColunaPivo($coeficientes)
    {
        $min = PHP_FLOAT_MAX;
        $coluna = -1;
        $maxIndex = count($coeficientes) - 1; // Índice máximo disponível
        foreach ($coeficientes as $j => $value) {
            if ($j > $maxIndex) continue; // Evitar índices fora do array
            if ($value < $min && $j !== 'termo' && abs($value) > $this->tolerancia) {
                $min = $value;
                $coluna = $j;
            }
        }
        return $coluna;
    }

    private function encontrarLinhaPivo($tabela, $coluna)
    {
        $minRazao = PHP_FLOAT_MAX;
        $linhaPivo = null;

        foreach ($tabela as $i => $linha) {
            if ($i === 0) continue; // Ignorar linha Z

            $coef = $linha['coeficientes'][$coluna];
            if ($coef <= 0) continue;

            $razao = $linha['termo'] / $coef;
            if ($razao < $minRazao) {
                $minRazao = $razao;
                $linhaPivo = $i;
            }
        }
        return $linhaPivo;
    }

    private function formatarIteracao($tabela, $colunaPivo, $linhaPivo, $passo)
    {
        $dados = [];
        foreach ($tabela as $i => $linha) {
            $dados[] = [
                'coeficientes' => $linha['coeficientes'],
                'termo' => $linha['termo'],
                'pivo' => ($i === $linhaPivo) ? $colunaPivo : null
            ];
        }
        return [
            'passo' => $passo,
            'tabela' => $dados,
            'colunaPivo' => $colunaPivo,
            'linhaPivo' => $linhaPivo
        ];
    }

    private function extrairSolucao($tabela)
    {
        $solucao = [];
        $numVariaveisOriginais = count($tabela[0]['coeficientes']) - (count($tabela) - 1); // Ajuste para variáveis originais

        for ($j = 0; $j < $numVariaveisOriginais; $j++) {
            $linhaBase = -1;
            $countUns = 0;

            foreach ($tabela as $i => $linha) {
                // Verificar se o índice existe antes de acessar
                if (!isset($linha['coeficientes'][$j])) {
                    continue; // Ignorar se não existir
                }

                $val = $linha['coeficientes'][$j];

                if (abs($val - 1) < $this->tolerancia) {
                    $countUns++;
                    $linhaBase = $i;
                } elseif (abs($val) > $this->tolerancia) {
                    $countUns = 0;
                    break;
                }
            }

            if ($countUns === 1) {
                $solucao['x' . ($j + 1)] = round($tabela[$linhaBase]['termo'], 2);
            } else {
                $solucao['x' . ($j + 1)] = 0;
            }
        }

        $solucao['Z'] = round($tabela[0]['termo'], 2);
        return $solucao;
    }
}
