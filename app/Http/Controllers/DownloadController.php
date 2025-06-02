<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Response;


class DownloadController extends Controller
{
    public function download(Request $request)
    {
        // dd($request->session()->all());

        // Recupera os dados da session.
        $tipo = $request->session()->get('tipo');
        $variaveis = $request->session()->get('variaveis');
        $restricoes = $request->session()->get('restricoes');
        $z = $request->session()->get('z');

        // Estrutura os dados.
        $dados = [
            "tipo" => $tipo,
            "variaveis" => $variaveis,
            "restricoes" => $restricoes,
            "z" => $z,
        ];

        // Transforma em JSON.
        $dadosJSON = json_encode($dados, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        // Define o formato do nome do arquivo.
        $filename = 'simplex_' . now()->format('Ymd_His') . '.json';

        // Retorna a resposta do download no navegador.
        return Response::make($dadosJSON, 200, [
            'Content-Type' => 'application/json',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]); //aqui pode levar um with( 'success' => 'Problema baixado com sucesso!') ???
    }
}
