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



        // GUARDA OS SINAIS E OS TERMOS EM ARRAYS
        $sinais = [];
        $termos = [];
        foreach ($restricoesData as $restricao) {
            foreach ($restricao as $key => $value) {
                if ($key == "sinal") {
                    $sinais[] = $value;
                }
                if ($key == "rhs") {
                    $termos[] = $value;
                }
            }
        }

        // ARRAY DAS RESTRIÇÕES SEM SINAIS E TERMOS
        $coeficientes = [];
        foreach ($restricoesData as $restricao) {
            unset($restricao["sinal"]);
            unset($restricao["rhs"]);
            $coeficientes[] = $restricao;
        }

        // MONTANDO O PROBLEMA

        dd($coeficientes);
        

        return $request;
    }
}