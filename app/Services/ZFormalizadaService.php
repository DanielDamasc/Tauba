<?php

namespace App\Services;



class ZFormalizadaService
{
    // Método que retira as variáveis artificiais da função objetivo.
    public function zFormalizada($tabela)
    {

        $bigM = app('bigM');

        // Verificando todas as artificiais que tem na Z.
        $copiaZ = $tabela[0];
        $pivotKeys = [];
        foreach ($copiaZ["coeficientes"] as $key => $value) {
            if ($value == -$bigM) {
                $pivotKeys[] = $key;
            }
        }

        // Fazendo a normalização para cada variável artificial encontrada.
        foreach ($pivotKeys as $pivotKey) {
            $copiaPivot = null;

            // Localizando a linha pivô associada.
            foreach ($tabela as $linhas) {
                if (isset($linhas["tipoVariavel"]["artificial"]) && $linhas["tipoVariavel"]["artificial"] == $pivotKey) {
                    $copiaPivot = $linhas;
                    break;
                }
            }

            // Normalizando a função Z.
            if ($copiaPivot) {
                foreach ($tabela[0]["coeficientes"] as $key => $value) {
                    $tabela[0]["coeficientes"][$key] += $bigM * $copiaPivot["coeficientes"][$key];
                }
                $tabela[0]["termo"] += $bigM * $copiaPivot["termo"];
            }
        }

        return $tabela;
    }
}
