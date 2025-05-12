<?php

namespace App\Services;



class FormaAumentadaService
{
    // ESTE MÉTODO DEVE RETORNAR OS DADOS DO PROBLEMA NA SUA FORMA AUMENTADA E ESTRUTURADOS.
    public function formaAumentada($request) {

        // dd($request->all());

        // DADOS DO REQUEST
        $tipo = $request->input('tipo'); // string
        $variaveis = (int) $request->input('variaveis'); // int
        $restricoes = count($request->input('restricoes')); // int 
        $z = $request->input('z'); // array
        $restricoesData = $request->input('restricoes'); // array de array

        // MOSTRANDO O PROBLEMA
        foreach ($z as $zElement) {
            // imprime cada elemento da linha Z
            echo $zElement . " ";
        }
        echo "<br>";
        foreach ($restricoesData as $restricao) {
            foreach ($restricao as $data) {
                // imprime os dados de cada restrição
                echo $data . " ";
            }
            echo "<br>";
        }

        // JUNTANDO Z E RESTRIÇÕES
        $restricoesData[0] = $z;
        ksort($restricoesData);

        // RETIRANDO SINAIS E TERMOS DO ARRAY
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

        // ESTRUTURANDO O PROBLEMA
        $problemaEstruturado = [];

        foreach ($coeficientes as $linha => $valor) {

            $problemaEstruturado[] = [
                'coeficientes' => $valor,
                'sinal' => $sinais[$linha],
                'termo' => $termos[$linha]
            ];
        }

        // AUMENTANDO O PROBLEMA
        $VF = 1;
        $VE = -1;
        $VA = 1;
        $bigM = 1000000000; // 1 bilhão para M
        $Xprox = $variaveis + 1;
        $Xcont = 0;
        $problema = [];
        $copiaZ = $problemaEstruturado[0]; // cópia da função objetivo.
        foreach ($problemaEstruturado as $line => $value) {
            
            if ($value["sinal"] == null) {
                continue;
            }

            // cópia das restrições para evitar manipulação por referência.
            $copiaLinha = $value;

            for ($i = $Xprox; $i < $Xprox + $Xcont; $i++) {
                if (!isset($copiaLinha["coeficientes"][$i])) {
                    $copiaLinha["coeficientes"][$i] = 0;
                }
            }
            
            switch ($value["sinal"]) {
                case "<=":
                    $copiaLinha["coeficientes"][$Xprox + $Xcont] = $VF;
                    $copiaZ["coeficientes"][$Xprox + $Xcont] = 0;
                    $Xcont++;
                    break;

                case "=":
                    $copiaLinha["coeficientes"][$Xprox + $Xcont] = $VA;
                    $copiaZ["coeficientes"][$Xprox + $Xcont] = $bigM;
                    $Xcont++;
                    break;

                case ">=":
                    $copiaLinha["coeficientes"][$Xprox + $Xcont] = $VE;
                    $copiaZ["coeficientes"][$Xprox + $Xcont] = 0;
                    $Xcont++;
                    $copiaLinha["coeficientes"][$Xprox + $Xcont] = $VA;
                    $copiaZ["coeficientes"][$Xprox + $Xcont] = $bigM;
                    $Xcont++;
                    break;

            }

            $problema[] = $copiaLinha;
        }
        array_unshift($problema, $copiaZ); // insere a Z no topo

        // CONVERTENDO OS DADOS PARA FLOAT
        foreach ($problema as &$prob) {
            $prob["coeficientes"] = array_map('floatval', $prob["coeficientes"]);

            $prob["termo"] = floatval($prob["termo"]);
        }
        unset($prob); // evita problemas relacionados a referência.

        // TAMANHO DA MAIOR RESTRIÇÃO
        $maior = -1;
        for ($i = 1; $i <= $restricoes; $i++) {
            $tamanho = count($problema[$i]["coeficientes"]);
            if ($tamanho > $maior) {
                $maior = $tamanho;
            }
        }

        // INSERINDO OS ZEROS DEPOIS
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