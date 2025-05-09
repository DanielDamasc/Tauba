<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SimplexController extends Controller
{
    // PROCESSA OS DADOS E CHAMA AS SERVICES PARA FORMA PADRÃO E RESOLUÇÃO DO ALGORITMO
    public function processar(Request $request) {

        // dd($request->all());

        // DADOS DO REQUEST
        $tipo = $request->input('tipo'); // string
        $variaveis = (int) $request->input('variaveis'); // int
        $restricoes = (int) $request->input('restricoes'); // int 
        $z = $request->input('z'); // array
        $restricoesData = $request->input('restricoes'); // array de array


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

    }
}
