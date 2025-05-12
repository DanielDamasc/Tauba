<?php

namespace App\Services;



class FormaAumentadaService
{
    // Método que retorna os dados estruturados e na forma aumentada. 
    public function formaAumentada($request) {

        $bigM = app('bigM');

        // Dados do request.
        $tipo = $request->input('tipo'); // string.
        $variaveis = (int) $request->input('variaveis'); // int.
        $restricoes = count($request->input('restricoes')); // int.
        $z = $request->input('z'); // array.
        $restricoesData = $request->input('restricoes'); // array de array.

        // Juntando função objetivo e as restrições.
        $restricoesData[0] = $z;
        ksort($restricoesData);

        // Retirando sinais e termos do array.
        $termos = [];
        $sinais = [];
        $coeficientes = [];
        $i = 0;
        foreach ($restricoesData as $rd) {
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
        $problemaEstruturado = [];

        foreach ($coeficientes as $linha => $valor) {

            $problemaEstruturado[] = [
                'coeficientes' => $valor,
                'sinal' => $sinais[$linha],
                'termo' => $termos[$linha]
            ];
        }

        // Aumentando o problema.
        $VF = 1;
        $VE = -1;
        $VA = 1;
        $Xprox = $variaveis + 1;
        $Xcont = 0;
        $problema = [];
        $copiaZ = $problemaEstruturado[0]; // Cópia da função objetivo.
        foreach ($problemaEstruturado as $line => $value) {
            
            if ($value["sinal"] == null) {
                continue;
            }

            // Cópia das restrições para evitar manipulação por referência.
            $copiaLinha = $value;

            for ($i = $Xprox; $i < $Xprox + $Xcont; $i++) {
                if (!isset($copiaLinha["coeficientes"][$i])) {
                    $copiaLinha["coeficientes"][$i] = 0;
                }
            }
            
            switch ($value["sinal"]) {
                case "<=":
                    $copiaLinha["coeficientes"][$Xprox + $Xcont] = $VF;
                    $copiaLinha["tipoVariavel"]["folga"] = $Xprox + $Xcont;
                    $copiaZ["coeficientes"][$Xprox + $Xcont] = 0;
                    $Xcont++;
                    break;

                case "=":
                    $copiaLinha["coeficientes"][$Xprox + $Xcont] = $VA;
                    $copiaLinha["tipoVariavel"]["artificial"] = $Xprox + $Xcont;
                    $copiaZ["coeficientes"][$Xprox + $Xcont] = $bigM;
                    $Xcont++;
                    break;

                case ">=":
                    $copiaLinha["coeficientes"][$Xprox + $Xcont] = $VE;
                    $copiaLinha["tipoVariavel"]["excesso"] = $Xprox + $Xcont;
                    $copiaZ["coeficientes"][$Xprox + $Xcont] = 0;
                    $Xcont++;
                    $copiaLinha["coeficientes"][$Xprox + $Xcont] = $VA;
                    $copiaLinha["tipoVariavel"]["artificial"] = $Xprox + $Xcont;
                    $copiaZ["coeficientes"][$Xprox + $Xcont] = $bigM;
                    $Xcont++;
                    break;

            }

            $problema[] = $copiaLinha;
        }
        array_unshift($problema, $copiaZ); // Insere a Z no topo.

        // Convertendo os dados para float.
        foreach ($problema as &$prob) {
            $prob["coeficientes"] = array_map('floatval', $prob["coeficientes"]);

            $prob["termo"] = floatval($prob["termo"]);
        }
        unset($prob); // Evita problemas relacionados a referência.

        // Retorna o tamanho da maior restrição.
        $maior = -1;
        for ($i = 1; $i <= $restricoes; $i++) {
            $tamanho = count($problema[$i]["coeficientes"]);
            if ($tamanho > $maior) {
                $maior = $tamanho;
            }
        }

        // Insere os 0's após com base neste tamanho.
        for ($i = 0; $i <= $restricoes; $i++) {
            for ($n = 1; $n <= $tamanho; $n++) {
                if ($i == 0 && $problema[$i]["coeficientes"][$n] != 0) {
                    $coef = $problema[$i]["coeficientes"][$n];
                    $coefNeg = -$coef;
                    $problema[$i]["coeficientes"][$n] = $coefNeg;
                }
                if (isset($problema[$i]["coeficientes"][$n])) {
                    continue;
                } else {
                    array_push($problema[$i]["coeficientes"], 0);
                }
            }
        }

        return $problema;
    }
}