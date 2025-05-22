<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class MontarController extends Controller
{
    public function montar(Request $request) {
        // Se os dados vierem dos input, retorna sem os dados da session.
        if (
        $request->input('tipo') != null &&
        $request->input('variaveis') != null &&
        $request->input('restricoes') != null
        ) {
            $tipo = $request->input('tipo');
            $variaveis = (int) $request->input('variaveis');
            $restricoes = (int) $request->input('restricoes');
            return view('simplex.montar', compact('tipo', 'variaveis', 'restricoes'));
    }
    
        // Se não for do input, então recupera e retorna os valores salvos na session.
        $tipo = $request->session()->get('tipo');
        $variaveis = (int) $request->session()->get('variaveis');
        $restricoesDados = $request->session()->get('restricoes');
        $restricoes = count($restricoesDados);
        $z = $request->session()->get('z');

        return view('simplex.montar', compact('tipo', 'variaveis', 'restricoesDados', 'restricoes', 'z'));
    }
}
