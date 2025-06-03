<?php

namespace App\Services;

use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class GerarGraficoService
{
    public function gerarGrafico($restricoes)
    {
        // Transforma em JSON.
        $restricoesJSON = json_encode($restricoes);

        // Caminho para o Python da venv.
        $pathVenv = "C:\laragon\www\Tauba\packages\Scripts\python.exe";

        // Caminho para o código.
        $path = "C:\laragon\www\Tauba\scripts\gerar_grafico.py";

        // Instancia a process e executa.
        $process = new Process([$pathVenv, $path, $restricoesJSON]);
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
