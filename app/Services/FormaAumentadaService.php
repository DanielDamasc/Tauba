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
                'valor' => $termos[$linha]
            ];
        }

        // AUMENTANDO O PROBLEMA
        $VF = 1;
        $VE = -1;
        $VA = 1;
        $Xprox = $variaveis + 1;
        $Xcont = 0;
        $problema = [];
        foreach ($problemaEstruturado as $line => $value) {
            $copiaLinha = $value; // cópia da estrutura da linha.
            if ($value["sinal"] == null) {
                $copiaZ = $value;
            }

            for ($i = $Xprox; $i < $Xprox + $Xcont; $i++) {
                if (!isset($copiaLinha["coeficientes"][$i])) {
                    $copiaLinha["coeficientes"][$i] = 0;
                }
            }
            
            if ($value["sinal"] != null) {
                switch ($value["sinal"]) {
                    case "<=":
                        $copiaLinha["coeficientes"][$Xprox + $Xcont] = $VF;
                        $Xcont++;
                        break;

                    case "=":
                        $copiaLinha["coeficientes"][$Xprox + $Xcont] = $VA;
                        $copiaZ["coeficientes"][$Xprox + $Xcont] = 
                        $Xcont++;
                        break;

                    case ">=":
                        $copiaLinha["coeficientes"][$Xprox + $Xcont] = $VE;
                        $Xcont++;
                        $copiaLinha["coeficientes"][$Xprox + $Xcont] = $VA;
                        $Xcont++;
                        break;

                }
            }

            $problema[] = $copiaLinha;

            
        }

        // CONVERTENDO OS DADOS PARA FLOAT
        foreach ($problema as &$prob) {
            $prob["coeficientes"] = array_map('floatval', $prob["coeficientes"]);

            $prob["valor"] = floatval($prob["valor"]);
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
                if ($i == 0 && isset($problema[$i]["coeficientes"][$n])) {
                    $coef = $problema[$i]["coeficientes"][$n];
                    $coef = -$coef;
                    $problema[$i]["coeficientes"][$n] = -$coef;
                }
                if (isset($problema[$i]["coeficientes"][$n])) {
                    continue;
                } else {
                    array_push($problema[$i]["coeficientes"], 0);
                }
            }
        }
        
        dd($problema);

        return $request;
    }
}