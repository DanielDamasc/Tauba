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

        // MOSTRANDO OS DADOS
        echo $tipo . "<br>"; // max ou min
        echo $variaveis . "<br>"; // quantidade de variaveis
        echo $restricoes . "<br>"; // quantidade de restrições
        echo "<br>";
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

        // DADOS COM Z E RESTRIÇÕES JUNTOS
        $restricoesData[0] = $z;
        ksort($restricoesData);

        // TESTES DE COMO EU VOU ADICIONAR AS VARIÁVEIS DE FOLGA
        // $variavelFolga = '1';
        // $variavelArtificial = '1';
        // $variavelExcesso = '-1';
        // $contVar = 0;
        // $arrTeste = array_map(function($linhasProb) use ($variavelFolga, $variavelArtificial, $variavelExcesso, $contVar, $variaveis) {
        //     // fazer condição para dar push das variáveis conforme a restrição ...
        //     if (isset($linhasProb["sinal"])) {
        //         switch ($linhasProb["sinal"]) {
        //             case "<=":
        //                 for ($i = 0; $i < $contVar; $i++) {
        //                     array_push($linhasProb, '0');
        //                 }
        //                 $contVar++;
        //                 array_push($linhasProb, $variavelFolga);
        //                 break;

        //             case "=":
        //                 for ($i = 0; $i < $contVar; $i++) {
        //                     array_push($linhasProb, '0');
        //                 }
        //                 $contVar++;
        //                 array_push($linhasProb, $variavelArtificial);
        //                 break;

        //             case ">=":
        //                 for ($i = 0; $i < $contVar; $i++) {
        //                     array_push($linhasProb, '0');
        //                 }
        //                 $contVar += 2;
        //                 array_push($linhasProb, $variavelExcesso, $variavelArtificial);
        //                 break;
        //         }
        //     }
        //    return [$linhasProb, $contVar];
        // }, $restricoesData);

        $variavelFolga = '1';
        $variavelExcesso = '-1';
        $variavelArtificial = '1';
        $contVar = 0;
        $arrResultado = [];
        $numVar = $variaveis + 1;

        foreach ($restricoesData as $index => $restricao) {
            $linha = $restricao; // cópia da linha

            // adiciona zeros correspondentes às variáveis já adicionadas em restrições anteriores
            for ($i = $numVar; $i < $contVar + $numVar; $i++) {
                $linha["$i"] = '0';
            }

            // adicionar variáveis conforme o sinal da restrição
            if (isset($restricao["sinal"])) {
                switch ($restricao["sinal"]) {
                    case "<=":
                        $num = $contVar + $numVar;
                        $linha["$num"] = $variavelFolga;
                        $contVar++;
                        break;

                    case "=":
                        $num = $contVar + $numVar;
                        $linha["$num"] = $variavelArtificial;
                        $contVar++;
                        break;

                    case ">=":
                        $num = $contVar + $numVar;
                        $linha["$num"] = $variavelExcesso;
                        $contVar++;
                        $num = $contVar + $numVar;
                        $linha["$num"] = $variavelArtificial;
                        $contVar++;
                        break;
                }
            }

            $arrResultado[] = $linha;
        }

        return $request;
    }
}