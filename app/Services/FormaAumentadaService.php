<?php

namespace App\Services;



class FormaAumentadaService
{
    // Método que retorna os dados estruturados e na forma aumentada. 
    public function formaAumentada($request)
    {

        $bigM = app('bigM');

        // Dados do request.
        $tipo = $request->input('tipo'); // string.
        $variaveis = (int) $request->input('variaveis'); // int.
        $restricoes = count($request->input('restricoes')); // int.
        $z = $request->input('z'); // array.
        $restricoesData = $request->input('restricoes'); // array de array.

        // Separar função objetivo das restrições
        $zLinha = $z;
        $restricoesData = array_values($restricoesData); // Resetar índices
        array_unshift($restricoesData, $zLinha); // Adicionar Z no início

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
            // Preencher com zeros até o número de variáveis originais
            $coefLinha = [];
            for ($k = 0; $k < $variaveis; $k++) {
                $coefLinha[$k] = $rd[$k] ?? 0;
            }
            $coeficientes[] = $coefLinha;
            
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
                    $copiaZ = $problemaEstruturado[0];
                    $Xcont++;
                    break;

                case "=":
                    $copiaLinha["coeficientes"][$Xprox + $Xcont] = $VA;
                    $copiaLinha["tipoVariavel"]["artificial"] = $Xprox + $Xcont;
                    $copiaZ = $problemaEstruturado[0];
                    $Xcont++;
                    break;

                case ">=":
                    $copiaLinha["coeficientes"][$Xprox + $Xcont] = $VE;
                    $copiaLinha["tipoVariavel"]["excesso"] = $Xprox + $Xcont;
                    $copiaZ = $problemaEstruturado[0];
                    $Xcont++;
                    $copiaLinha["coeficientes"][$Xprox + $Xcont] = $VA;
                    $copiaLinha["tipoVariavel"]["artificial"] = $Xprox + $Xcont;
                    $copiaZ = $problemaEstruturado[0];
                    $Xcont++;
                    break;
            }

            $problema[] = $copiaLinha;
        }
        array_unshift($problema, $copiaZ); // Insere a Z no topo.

        // Garantir que todos os coeficientes tenham o mesmo tamanho
        $maxColunas = max(array_map('count', array_column($problema, 'coeficientes')));
        foreach ($problema as &$linha) {
            while (count($linha['coeficientes']) < $maxColunas) {
                array_push($linha['coeficientes'], 0);
            }
            ksort($linha['coeficientes']); // Ordenar as chaves
        }
        unset($linha);

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
        // Retorna o tamanho da maior restrição
        $tamanho = count($problema[0]["coeficientes"]); // Baseado na linha Z

        // Insere os 0's após com base neste tamanho
        for ($i = 0; $i <= $restricoes; $i++) {
            // Reindexar coeficientes
            if ($i == 0) {
                for ($n = 0; $n < $variaveis; $n++) {
                    $problema[$i]["coeficientes"][$n] *= -1;
                }
            }

            // Preencher com zeros até o tamanho máximo
            $problema[$i]["coeficientes"] = array_pad(
                $problema[$i]["coeficientes"],
                $tamanho,
                0
            );
        }
        $maxColunas = max(array_map('count', array_column($problema, 'coeficientes')));
        foreach ($problema as &$linha) {
            while (count($linha['coeficientes']) < $maxColunas) {
                array_push($linha['coeficientes'], 0);
            }
        }
        unset($linha);

        return $problema;
    }
}
