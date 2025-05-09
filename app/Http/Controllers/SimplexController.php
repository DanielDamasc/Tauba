<?php

namespace App\Http\Controllers;

use App\Services\FormaAumentadaService;
use Illuminate\Http\Request;

class SimplexController extends Controller
{
    // PROCESSA OS DADOS E CHAMA AS SERVICES PARA FORMA PADRÃO E RESOLUÇÃO DO ALGORITMO
    public function processar(Request $request) {

        $dadosFormatados = (new FormaAumentadaService())->formaAumentada($request);

    }
}
