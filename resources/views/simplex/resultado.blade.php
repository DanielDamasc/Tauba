<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Resultado da Solução</title>
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
            background-color: #eff6ff;
            border-left: 4px solid #3b82f6;
            padding: 1rem;
            margin-bottom: 1rem;
            border-radius: 0.5rem;
            color: #1e40af;
        }
        .node-card {
            background-color: white;
            border: 1px solid #e5e7eb;
            border-left-width: 4px;
            border-radius: 0.5rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
        }
        .node-header {
            padding: 0.75rem 1.25rem;
            background-color: #f9fafb;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .node-body {
            padding: 1.25rem;
        }
        .status-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.875rem;
            font-weight: 600;
            text-transform: uppercase;
        }
        .status-ramificando { background-color: #dbeafe; color: #1e40af; }
        .status-podado_por_limite { background-color: #fef3c7; color: #92400e; }
        .status-podado_por_inviabilidade { background-color: #fee2e2; color: #991b1b; }
        .status-solucao_inteira_encontrada { background-color: #dcfce7; color: #14532d; }
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
            <h1 class="mx-12 mb-2 text-5xl font-extrabold tracking-tight text-[#5C3A21]">
                @if($is_branch_and_bound ?? false)
                    Resultado do Branch and Bound
                @else
                    Resultado da Solução Algébrica
                @endif
            </h1>
            <p class="mb-12 text-lg text-amber-950 sm:text-xl">
                 @if($is_branch_and_bound ?? false)
                    Abaixo está a árvore de decisão e as iterações para encontrar a solução inteira.
                @else
                    Abaixo estão as iterações do método Simplex.
                @endif
            </p>
        </header>

        {{-- Lógica de Exibição --}}
        @if($is_branch_and_bound ?? false)
            {{-- Exibição para Branch and Bound --}}
            @foreach($iteracoesBranchAndBound as $node)
                <div class="node-card" style="border-color: 
                    @if($node['status'] == 'ramificando') #60a5fa;
                    @elseif(str_contains($node['status'], 'podado')) #fca5a5;
                    @elseif($node['status'] == 'solucao_inteira_encontrada') #4ade80;
                    @endif
                ">
                    <div class="node-header">
                        <h2 class="text-xl font-bold text-gray-800">
                            Nó #{{ $node['id'] }} 
                            <span class="text-base font-medium text-gray-500">(Pai: #{{$node['parentId']}})</span>
                        </h2>
                        <span class="status-badge status-{{$node['status']}}">{{ str_replace('_', ' ', $node['status']) }}</span>
                    </div>
                    <div class="node-body">
                        <p class="mb-4 text-lg text-gray-700"><strong>Condição da Ramificação:</strong> {{ $node['branch'] }}</p>
                        
                        @if($node['motivoPoda'])
                            <p class="p-3 mb-4 text-yellow-800 bg-yellow-100 border-l-4 border-yellow-500 rounded-md">
                                <strong>Motivo da Poda/Atualização:</strong> {{ $node['motivoPoda'] }}
                            </p>
                        @endif

                        @if(isset($node['resultadoSimplex']['solucao']))
                            <div class="p-4 mb-4 bg-gray-50 rounded-lg">
                                <h4 class="mb-2 text-lg font-semibold text-gray-800">Solução Relaxada do Nó:</h4>
                                <p class="text-2xl font-bold text-center text-gray-700">
                                    Z = <span class="text-blue-600">{{ number_format($node['resultadoSimplex']['solucao']['Z'] ?? 0, 4) }}</span>
                                </p>
                                <div class="mt-3 space-y-1 text-base text-center text-gray-600">
                                    @foreach ($node['resultadoSimplex']['solucao'] as $var => $valor)
                                        @if ($var !== 'Z')
                                            <span>{{ $var }} = <span class="font-semibold">{{ number_format($valor, 4) }}</span></span>{{ !$loop->last ? ',' : '' }}
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                        @else
                             <p class="p-3 text-red-800 bg-red-100 border-l-4 border-red-500 rounded-md">
                                O problema relaxado neste nó é <strong>{{ $node['resultadoSimplex']['status'] }}</strong>.
                            </p>
                        @endif
                    </div>
                </div>
            @endforeach
        @else
            {{-- Exibição para Simplex Padrão --}}
            @foreach ($iteracoes as $iteracao)
            <div class="overflow-hidden bg-white border border-orange-100 shadow-lg rounded-2xl">
                <div class="px-6 py-4 text-lg font-semibold text-amber-950 bg-orange-100">
                    Iteração {{ $iteracao['passo'] }}
                    @if ($iteracao['colunaPivo'] !== null)
                    - Encontrando Pivô
                    @else
                    - Fim
                    @endif
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-lg text-center">
                        <thead class="text-amber-900 bg-orange-50">
                            <tr>
                                <th class="p-4 text-left">Base</th>
                                @foreach ($iteracao['tabela'][0]['coeficientes'] as $key => $value)
                                <th class="p-4">x{{ $key + 1 }}</th>
                                @endforeach
                                <th class="p-4">b</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($iteracao['tabela'] as $i => $linha)
                            <tr class="border-b">
                                <td class="p-4 font-semibold text-left text-amber-800">
                                    {{ $i === 0 ? 'Z' : 'x' . ($i) }} {{-- Simplificação --}}
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
                 @if(isset($iteracao['mensagem']))
                    <div class="p-4 text-sm text-center text-gray-600 bg-gray-50">
                        {{$iteracao['mensagem']}}
                    </div>
                @endif
            </div>
            @endforeach
        @endif
        
        {{-- Resultado Final --}}
        <div class="max-w-2xl p-8 mx-auto mt-12 shadow-xl rounded-2xl
             @if(isset($status) && ($status === 'otimo' || $status === 'otimo_inteiro')) bg-white border-l-8 border-green-500 
             @elseif(isset($status) && $status === 'ilimitado') bg-yellow-50 border-l-8 border-yellow-400
             @elseif(isset($status) && (str_starts_with($status, 'erro_') || $status === 'otimo_ou_erro_coluna_pivo' || $status === 'sem_solucao_inteira')) bg-red-50 border-l-8 border-red-400
             @else bg-gray-50 border-l-8 border-gray-400 @endif">
            
            @if (isset($status) && ($status === 'otimo' || $status === 'otimo_inteiro') && isset($solucao) && is_array($solucao))
                <div class="flex items-center justify-center gap-3 mb-4 text-3xl font-extrabold text-green-700">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-10 h-10">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Solução Ótima Encontrada @if($status === 'otimo_inteiro')(Inteira)@endif
                </div>
                <p class="mt-2 text-3xl font-bold text-center text-gray-800">
                    Z = <span class="text-green-700">{{ number_format($solucao['Z'] ?? 0, 4) }}</span>
                </p>
                @if (count(array_filter(array_keys($solucao), fn($k) => $k !== 'Z')) > 0)
                    <p class="mt-5 text-xl text-center text-gray-700">Valores das variáveis:</p>
                    <div class="mt-3 space-y-1 text-lg text-center text-gray-600">
                        @foreach ($solucao as $var => $valor)
                            @if ($var !== 'Z')
                                <span>{{ $var }} = <span class="font-semibold text-green-600">{{ number_format($valor, 4) }}</span></span>{{ !$loop->last ? ',' : '' }}
                            @endif
                        @endforeach
                    </div>
                @endif
            @elseif (isset($status) && $status === 'sem_solucao_inteira')
                 <div class="flex items-center justify-center gap-3 mb-4 text-3xl font-extrabold text-red-700">
                     <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-10 h-10"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" /></svg>
                    Nenhuma Solução Inteira
                </div>
                 <p class="mt-4 text-xl text-center text-red-800">
                    Não foi possível encontrar uma solução viável inteira após explorar toda a árvore de decisão.
                </p>
            @elseif (isset($status) && $status === 'ilimitado')
                 {{-- ... (código existente para ilimitado) ... --}}
            @else
                 {{-- ... (código existente para outros erros) ... --}}
            @endif
        </div>
        
        {{-- Botão para salvar --}}
        <div class="flex justify-center">
            <a onclick="toastr.success('Sucesso: problema baixado!')" href="{{ route('simplex.download') }}"
                class="px-6 py-3 text-lg font-semibold text-[#5C3A21] bg-white/30 border border-white/60 rounded-full shadow backdrop-blur hover:bg-white/40 transition-all duration-300">
                📂 <span>Salvar problema</span>
            </a>
        </div>
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