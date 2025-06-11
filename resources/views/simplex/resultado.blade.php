<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Táuba</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;900&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }

        .cell-red {
            background-color: #fee2e2;
            color: #b91c1c;
            font-weight: 600;
        }

        .cell-green {
            background-color: #dcfce7;
            color: #15803d;
            font-weight: 600;
        }

        .cell-pivot {
            background-color: #fef9c3;
            border: 2px solid #facc15;
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

<body class="bg-gradient-to-br from-[#C69C6D] via-[#ffe4c6] to-[#B88960] min-h-screen py-12 px-6">
    <!-- Botão Voltar -->

    <div class="absolute top-6 left-6 flex flex-col gap-2">
        <a href="{{ route('simplex.index') }}" class="duration-300 hover:scale-110">
            <img class="w-8" src="{{ asset('assets/home.svg') }}" alt="Voltar para a página inicial" title="Voltar para a página inicial">
        </a>
        <a href="{{ route('simplex.montar') }}" class="duration-300 hover:scale-110">
            <img class="w-8" src="{{ asset('assets/back.svg') }}" alt="Voltar para a etapa anterior" title="Voltar para a etapa anterior">
        </a>
    </div>

    <div class="mx-auto space-y-16 max-w-7xl">

        {{-- Cabeçalho --}}
        <header class="text-center">
            <h1 class="mb-2 text-5xl font-extrabold tracking-tight text-[#5C3A21]">Resultado da Solução Algébrica</h1>
            <p class="mb-12 text-lg text-amber-950 sm:text-xl">Abaixo estão as iterações do método Simplex</p>
        </header>

        {{-- Iterações (Exemplo Estático) --}}
        @foreach ($iteracoes as $iteracao)
        <div class="overflow-hidden bg-white border border-orange-100 shadow-lg rounded-2xl">
            <div class="px-6 py-4 text-lg font-semibold text-amber-950 bg-orange-100">
                Iteração {{ $iteracao['passo'] }}
                @if ($iteracao['colunaPivo'] !== null)
                - Encontrando Pivô
                @else
                - Solução Ótima
                @endif
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full text-lg text-center">
                    <thead class="text-amber-900 bg-orange-50">
                        <tr>
                            <th class="p-4 text-left">Variável</th>
                            @foreach ($iteracao['tabela'][0]['coeficientes'] as $key => $value)
                            <th class="p-4">x{{ $key + 1 }}</th>
                            @endforeach
                            <th class="p-4">Solução</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($iteracao['tabela'] as $i => $linha)
                        <tr class="border-b">
                            <td class="p-4 font-semibold text-left text-amber-800">
                                {{ $i === 0 ? 'Z' : 'R' . $i }}
                            </td>
                            @foreach ($linha['coeficientes'] as $j => $coef)
                            <td
                                class="text-amber-950 p-3 
                            @if ($j == $iteracao['colunaPivo'] && $i == $iteracao['linhaPivo']) cell-pivot
                            @elseif ($j == $iteracao['colunaPivo']) cell-red @endif">
                                {{ number_format($coef, 2) }}
                            </td>
                            @endforeach
                            <td class="p-3 text-amber-950">{{ number_format($linha['termo'], 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endforeach

        {{-- Resultado Final --}}
        <div class="max-w-2xl p-8 mx-auto mt-12 shadow-xl rounded-2xl Add commentMore actions
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

        {{-- Botão para salvar um problema no filesystem. --}}
        <div class="flex justify-center">
            <a onclick="toastr.success('Sucesso: problema baixado!')" href="{{ route('simplex.download') }}"
                class="px-6 py-3 text-lg font-semibold text-[#5C3A21] bg-white/30 border border-white/60 rounded-full shadow backdrop-blur hover:bg-white/40 transition-all duration-300">
                📂 <span>Salvar problema</span>
            </a>
        </div>


        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    @if(isset($success))
        <script>
            toastr.success("{{ $success }}");
        </script>
    @endif

    @if(isset($error))
        <script>
            toastr.error("{{ $error }}");
        </script>
    @endif

    @if(isset($info))
        <script>
            toastr.info("{{ $info }}");
        </script>
    @endif

    @if(isset($warning))
        <script>
            toastr.warning("{{ $warning }}");
        </script>
    @endif
</body>

</html>