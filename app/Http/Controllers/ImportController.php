<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ImportController extends Controller
{
    // Este controller trata da lógica de importação de problemas salvos do filesystem.
    public function importar(Request $request) {

        // Regras de validação para o arquivo, o usuário deve adicionar um arquivo JSON.
        $request->validate([
            'existing-problem' => 'required|file|mimes:json',
        ]);

        // Pega o arquivo.
        $arquivo = $request->file('existing-problem');

        // Pega os dados do arquivo.
        $conteudo = file_get_contents($arquivo->getRealPath());

        // Faz decode no JSON.
        $dadosOriginais = json_decode($conteudo, true);

        // Recebe os dados que precisam passar para a view.
        $tipo = $dadosOriginais['tipo'];
        $variaveis = (int) $dadosOriginais['variaveis'];
        $restricoesDados = $dadosOriginais['restricoes'];
        $restricoes = count($restricoesDados);
        $z = $dadosOriginais['z'];

        // Coloca o problema importado na session.
        $request->session()->put('tipo', $dadosOriginais['tipo']);
        $request->session()->put('variaveis', $dadosOriginais['variaveis']);
        $request->session()->put('restricoes', $dadosOriginais['restricoes']);
        $request->session()->put('z', $dadosOriginais['z']);

        // Retorna os dados e a rota de montar o problema.
        return view('simplex.montar', compact('tipo', 'variaveis', 'restricoesDados', 'restricoes', 'z'));
    }
}
