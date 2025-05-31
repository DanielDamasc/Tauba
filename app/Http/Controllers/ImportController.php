<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ImportController extends Controller
{
    // Este controller trata da lógica de importação de problemas salvos do filesystem.
    public function importar(Request $request)
    {
        try {
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

            // Retorna os dados importados e a rota de montar o problema com a mensagem do toastr.
            return view('simplex.montar', compact('tipo', 'variaveis', 'restricoesDados', 'restricoes', 'z') + ['success' => 'Problema importado com sucesso!']);
        } catch (\Exception) {
            // Se der erro, retorna para a view de importar com uma mensagem de erro.
            return view('simplex.escolha', ['warning' => 'Atenção: o arquivo não é um JSON válido.']);
        }
    }
}
