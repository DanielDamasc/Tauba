<?php

namespace App\Services;



class EstruturaGraficoService
{
    public function estruturaGrafico($dadosRestricoes)
    {
        // Retirando sinais e termos do array.
        $termos = [];
        $sinais = [];
        $coeficientes = [];
        $i = 0;
        foreach ($dadosRestricoes as $rd) {
            if (!isset($rd["rhs"])) {
                $termos[$i] = null;
            }
            if (!isset($rd["sinal"])) {
                $sinais[$i] = null;
            }
            if (isset($rd["rhs"])) {
                $termos[$i] = array_pop($rd);
            }
            if (isset($rd["sinal"])) {
                $sinais[$i] = array_pop($rd);
            }
            $coeficientes[] = $rd;
            $i++;
        }

        // Estruturando o problema.
        $estrutura = [];

        foreach ($coeficientes as $linha => $valor) {

            $estrutura[] = [
                'coeficientes' => $valor,
                'sinal' => $sinais[$linha],
                'termo' => $termos[$linha]
            ];
        }

        return $estrutura;
    }
}
