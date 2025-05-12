<?php

namespace App\Services;



class SimplexMaxService
{
    // Método que retorna a solução ótima do método simplex.
    public function simplexMax($tabela) {

        dd($tabela);

        $bigM = app('bigM');

        // Verificar se a linha Z precisa de normalização.
    }
}