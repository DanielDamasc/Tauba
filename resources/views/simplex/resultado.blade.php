<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Resultado Simplex - Táuba</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;900&display=swap" rel="stylesheet" />
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }

        .cell-red { /* Estilo para coluna pivô (exceto elemento pivô) */
            background-color: #fee2e2; /* bg-red-100 */
            color: #b91c1c; /* text-red-700 */
            font-weight: 600;
        }

        .cell-green { /* Não usado atualmente, mas pode ser útil para linha pivô */
            background-color: #dcfce7; /* bg-green-100 */
            color: #15803d; /* text-green-700 */
            font-weight: 600;
        }

        .cell-pivot { /* Estilo para o elemento pivô */
            background-color: #fef9c3; /* bg-yellow-100 */
            border: 2px solid #facc15; /* border-yellow-400 */
            font-weight: bold;
        }
        .message-info {
            background-color: #eff6ff; /* bg-blue-50 */
            border-left: 4px solid #3b82f6; /* border-blue-500 */
            padding: 1rem;
            margin-bottom: 1rem;
            border-radius: 0.5rem;
            color: #1e40af; /* text-blue-700 */
        }
        .message-warning {
            background-color: #fffbeb; /* bg-yellow-50 */
            border-left: 4px solid #f59e0b; /* border-yellow-500 */
            padding: 1rem;
            margin-bottom: 1rem;
            border-radius: 0.5rem;
            color: #b45309; /* text-yellow-700 */
        }
        .message-error {
            background-color: #fef2f2; /* bg-red-50 */
            border-left: 4px solid #ef4444; /* border-red-500 */
            padding: 1rem;
            margin-bottom: 1rem;
            border-radius: 0.5rem;
            color: #b91c1c; /* text-red-700 */
        }
    </style>
</head>

<body class="bg-gradient-to-br from-[#f0f4ff] via-[#e1ecf7] to-[#dce9f5] min-h-screen py-12 px-6">

    <a href="{{ url()->previous() }}"
        class="absolute top-6 right-6 inline-flex items-center gap-2 px-5 py-2.5 text-sm font-semibold text-white bg-indigo-600 rounded-full shadow hover:bg-indigo-700 transition-all duration-300">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" />
        </svg>
        Voltar para a montagem
    </a>

    <div class="mx-auto space-y-12 max-w-7xl">

        {{-- Cabeçalho --}}
        <header class="text-center">
            <h1 class="mb-2 text-5xl font-extrabold text-gray-900">Resultado da Solução Algébrica</h1>
            <p class="text-lg text-gray-600">Abaixo estão as iterações do método Simplex e o resultado final.</p>
        </header>

        {{-- Iterações --}}
        @if (isset($iteracoes) && is_array($iteracoes) && count($iteracoes) > 0)
            @foreach ($iteracoes as $iteracao)
                <div class="overflow-hidden bg-white border border-gray-200 shadow-lg rounded-2xl">
                    <div class="px-6 py-4 text-lg font-semibold text-gray-800 bg-indigo-100 rounded-t-2xl">
                        Iteração {{ $iteracao['passo'] ?? 'N/A' }}
                        @if (isset($iteracao['mensagem']) && !empty($iteracao['mensagem']))
                            <span class="block text-sm font-normal text-indigo-700">{{ $iteracao['mensagem'] }}</span>
                        @elseif (isset($iteracao['colunaPivo']) && $iteracao['colunaPivo'] !== null && isset($iteracao['linhaPivo']) && $iteracao['linhaPivo'] !== null)
                            <span class="text-sm font-normal text-indigo-600">- Elemento Pivô: {{ number_format($iteracao['elementoPivoValorOriginal'] ?? 0, 2) }} (Linha {{ ($iteracao['linhaPivo'] ?? -1) + 0 }}, Coluna x{{ ($iteracao['colunaPivo'] ?? -1) + 1 }})</span>
                        @elseif (isset($iteracao['colunaPivo']) && $iteracao['colunaPivo'] !== null)
                             <span class="text-sm font-normal text-indigo-600">- Selecionando Linha Pivô (Coluna x{{ ($iteracao['colunaPivo'] ?? -1) + 1 }})</span>
                        @endif
                    </div>
                    @if (isset($iteracao['tabela']) && is_array($iteracao['tabela']) && count($iteracao['tabela']) > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full text-base text-center">
                                <thead class="text-gray-700 bg-indigo-50">
                                    <tr>
                                        <th class="p-3 text-left">Base</th>
                                        @if (isset($iteracao['tabela'][0]['coeficientes']) && is_array($iteracao['tabela'][0]['coeficientes']))
                                            @foreach ($iteracao['tabela'][0]['coeficientes'] as $key => $value)
                                                <th class="p-3">x{{ $key + 1 }}</th>
                                            @endforeach
                                        @endif
                                        <th class="p-3">Termo (RHS)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($iteracao['tabela'] as $i => $linha)
                                        <tr class="border-b border-gray-200 last:border-b-0 hover:bg-indigo-50/30 transition-colors duration-150">
                                            <td class="p-3 font-semibold text-left text-indigo-700">
                                                {{-- Tentar identificar a variável básica da linha --}}
                                                @php
                                                    $baseVar = ($i === 0) ? 'Z' : ('R' . $i); // Default
                                                    $oneFound = false;
                                                    $oneIndex = -1;
                                                    if ($i > 0 && isset($linha['coeficientes']) && is_array($linha['coeficientes'])) {
                                                        foreach($linha['coeficientes'] as $varIdx => $coefVal) {
                                                            if (abs($coefVal - 1.0) < 1e-6) { // Perto de 1
                                                                $isBasicCandidate = true;
                                                                // Verificar se outros na coluna são zero (exceto Z-row)
                                                                for($checkRow = 1; $checkRow < count($iteracao['tabela']); $checkRow++) {
                                                                    if ($checkRow != $i && isset($iteracao['tabela'][$checkRow]['coeficientes'][$varIdx]) && abs($iteracao['tabela'][$checkRow]['coeficientes'][$varIdx]) > 1e-6) {
                                                                        $isBasicCandidate = false;
                                                                        break;
                                                                    }
                                                                }
                                                                if ($isBasicCandidate) {
                                                                    $baseVar = 'x' . ($varIdx + 1);
                                                                    break;
                                                                }
                                                            }
                                                        }
                                                    }
                                                @endphp
                                                {{ $baseVar }}
                                            </td>
                                            @if (isset($linha['coeficientes']) && is_array($linha['coeficientes']))
                                                @foreach ($linha['coeficientes'] as $j => $coef)
                                                    <td class="p-3
                                                        @if (isset($iteracao['linhaPivo']) && isset($iteracao['colunaPivo']) && $j == $iteracao['colunaPivo'] && $i == $iteracao['linhaPivo']) cell-pivot
                                                        @elseif (isset($iteracao['colunaPivo']) && $j == $iteracao['colunaPivo'] && $i === 0) cell-red font-bold /* Indicador da coluna pivô na linha Z */
                                                        @elseif (isset($iteracao['colunaPivo']) && $j == $iteracao['colunaPivo']) cell-red /* Células da coluna pivô */
                                                        @elseif (isset($iteracao['linhaPivo']) && $i == $iteracao['linhaPivo']) cell-green /* Células da linha pivô */
                                                        @endif">
                                                        {{ number_format($coef, 2) }}
                                                    </td>
                                                @endforeach
                                            @else
                                                <td colspan="{{ isset($iteracao['tabela'][0]['coeficientes']) ? count($iteracao['tabela'][0]['coeficientes']) : 1 }}" class="p-3 text-gray-500 italic">Coeficientes não disponíveis</td>
                                            @endif
                                            <td class="p-3 font-medium @if(isset($iteracao['linhaPivo']) && $i == $iteracao['linhaPivo']) cell-green @endif">{{ isset($linha['termo']) ? number_format($linha['termo'], 2) : 'N/A' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="p-6 text-center text-gray-500">Nenhum dado de tabela para esta iteração.</div>
                    @endif
                </div>
            @endforeach
        @else
            <div class="p-6 text-center text-gray-500 bg-white border border-gray-200 shadow-lg rounded-2xl">
                Nenhuma iteração para exibir. Verifique os dados de entrada.
            </div>
        @endif

        {{-- Resultado Final --}}
        <div class="max-w-2xl p-8 mx-auto mt-12 shadow-xl rounded-2xl 
            @if(isset($status) && $status === 'otimo') bg-white border-l-8 border-green-500 
            @elseif(isset($status) && $status === 'ilimitado') bg-yellow-50 border-l-8 border-yellow-400
            @elseif(isset($status) && (str_starts_with($status, 'erro_') || $status === 'otimo_ou_erro_coluna_pivo')) bg-red-50 border-l-8 border-red-400
            @else bg-gray-50 border-l-8 border-gray-400 @endif">
            
            @if (isset($status) && $status === 'otimo' && isset($solucao) && is_array($solucao))
                <div class="flex items-center justify-center gap-3 mb-4 text-3xl font-extrabold text-green-700">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-10 h-10">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Solução Ótima Encontrada
                </div>
                <p class="mt-2 text-3xl font-bold text-center text-gray-800">
                    Z = <span class="text-green-700">{{ number_format($solucao['Z'] ?? 0, 2) }}</span>
                </p>
                @if (count(array_filter(array_keys($solucao), fn($k) => $k !== 'Z')) > 0)
                    <p class="mt-5 text-xl text-center text-gray-700">Valores das variáveis:</p>
                    <div class="mt-3 space-y-1 text-lg text-center text-gray-600">
                        @foreach ($solucao as $var => $valor)
                            @if ($var !== 'Z')
                                <span>{{ $var }} = <span class="font-semibold text-green-600">{{ number_format($valor, 2) }}</span></span>{{ !$loop->last ? ',' : '' }}
                            @endif
                        @endforeach
                    </div>
                @endif

            @elseif (isset($status) && $status === 'ilimitado')
                <div class="flex items-center justify-center gap-3 mb-4 text-3xl font-extrabold text-yellow-700">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-10 h-10">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                    </svg>
                    Problema Ilimitado
                </div>
                <p class="mt-4 text-xl text-center text-yellow-800">
                    A solução para o problema é ilimitada. Não há um valor ótimo finito.
                    @php
                        $lastIterationMessage = '';
                        if(isset($iteracoes) && count($iteracoes) > 0) {
                            $lastIter = end($iteracoes);
                            if(isset($lastIter['mensagem'])) $lastIterationMessage = $lastIter['mensagem'];
                        }
                    @endphp
                    @if(!empty($lastIterationMessage))
                        <span class="block mt-2 text-sm">{{ $lastIterationMessage }}</span>
                    @endif
                </p>

            @elseif (isset($status) && (str_starts_with($status, 'erro_') || $status === 'otimo_ou_erro_coluna_pivo'))
                <div class="flex items-center justify-center gap-3 mb-4 text-3xl font-extrabold text-red-700">
                     <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-10 h-10">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                    </svg>
                    Erro no Processamento
                </div>
                <p class="mt-4 text-xl text-center text-red-800">
                    Ocorreu um erro durante a execução do método Simplex.
                    @php
                        $detailedMessage = 'Consulte as iterações para mais detalhes.';
                        if(isset($iteracoes) && count($iteracoes) > 0) {
                            $lastIter = end($iteracoes);
                            if(isset($lastIter['mensagem'])) $detailedMessage = $lastIter['mensagem'];
                        } elseif (isset($mensagem)) { // Fallback to a general message if passed
                            $detailedMessage = $mensagem;
                        }
                    @endphp
                     <span class="block mt-2 text-sm">{{ $detailedMessage }} (Status: {{ $status }})</span>
                </p>
            @else
                <div class="flex items-center justify-center gap-3 mb-4 text-3xl font-extrabold text-gray-700">
                    Status Desconhecido
                </div>
                <p class="mt-4 text-xl text-center text-gray-800">
                    Não foi possível determinar o resultado final do problema. Status: {{ $status ?? 'Não disponível' }}.
                </p>
            @endif
        </div>
    </div>

</body>
</html>