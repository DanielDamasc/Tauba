<?php

namespace App\Services;

use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class GerarGraficoService
{
    public function gerarGrafico($problema)
    {
        // Transforma em JSON.
        $problemaJSON = json_encode($problema);

        // Caminho para o Python da venv.
        $pathVenv = base_path('packages\Scripts\python.exe');

        // Caminho para o código.
        $path = base_path('scripts\gerar_grafico.py');

        // Instancia a process e executa.
        $process = new Process([$pathVenv, $path, $problemaJSON]);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new \RunTimeException($process->getErrorOutput());
        }

        // Captura o que é printado no terminal do script.
        $output = $process->getOutput();

        // Transforma JSON em array associativo.
        $resultado = json_decode($output, true);

        return $resultado;
    }
}
