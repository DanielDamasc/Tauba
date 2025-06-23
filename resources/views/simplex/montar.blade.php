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

        input[type="number"]::-webkit-inner-spin-button {
            appearance: none;
        }
    </style>
</head>

<body
    class="relative flex flex-col items-center justify-center min-h-screen bg-gradient-to-br from-[#C69C6D] via-[#ffe4c6] to-[#B88960]">

    <div class="absolute top-6 left-6 flex flex-col gap-2">
        <a href="{{ route('simplex.index') }}" class="duration-300 hover:scale-110">
            <img class="w-8" src="{{ asset('assets/home.svg') }}" alt="Voltar para a página inicial" title="Voltar para a página inicial">
        </a>
        <a href="{{ route('simplex.escolha') }}" class="duration-300 hover:scale-110">
            <img class="w-8" src="{{ asset('assets/back.svg') }}" alt="Voltar para a etapa anterior" title="Voltar para a etapa anterior">
        </a>
    </div>
    <div class="mx-auto max-w-7xl">
        <header class="mb-12 text-center mx-6">
            <h1 class="mx-12 mb-2 text-5xl font-extrabold tracking-tight text-[#5C3A21]">Montar Problema</h1>
            <p class="mb-12 text-lg text-amber-950 sm:text-xl">
                Informe os coeficientes da função objetivo e das restrições para a
                <span class="font-semibold text-amber-800">{{ $tipo === 'min' ? 'minimização' : 'maximização' }}</span>.
            </p>
        </header>
        <form action="{{ route('simplex.resolver') }}" method="POST">
            @csrf
            <input type="hidden" name="tipo" value="{{ $tipo }}">
            <input type="hidden" name="variaveis" value="{{ $variaveis }}">
            <input type="hidden" name="restricoes" value="{{ $restricoes }}">

            <!-- Tabela de entrada -->
            <div class="mx-2 overflow-x-auto bg-white border border-orange-100 shadow-xl rounded-2xl">
                <table class="min-w-full text-lg text-center">
                    <thead class="text-amber-900 bg-orange-100">
                        <tr>
                            <th class="px-6 py-4 text-left">Função</th>
                            @for ($v = 1; $v <= $variaveis; $v++)
                                <th class="px-6 py-4">x{{ $v }}</th>
                                @endfor
                                <th class="px-6 py-4">Sinal</th>
                                <th class="px-6 py-4">RHS</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Linha Z -->
                        <tr class="border-b bg-orange-50">
                            <td class="px-6 py-4 font-bold text-left text-amber-800">Z</td>
                            @for ($v = 1; $v <= $variaveis; $v++)
                                <td class="px-4 py-3">
                                <input value="{{ old('z.' . $v, $z[$v] ?? '') }}" type="number" step="any"
                                    name="z[{{ $v }}]" placeholder="0"
                                    class="w-full px-4 py-2 text-center text-[#5C3A21] border border-[#A9745B] shadow-sm rounded-xl focus:ring-2 bg-[#FDF5E6] focus:ring-[#8B5E3C] placeholder-[#a3785c] focus:outline-none" />
                                </td>
                                @endfor
                                <td></td>
                                <td></td>
                        </tr>

                        <!-- Restrições -->
                        @for ($r = 1; $r <= $restricoes; $r++)
                            <tr class="border-b bg-white hover:bg-orange-100">
                            <td class="px-6 py-4 font-semibold text-left text-amber-900">R{{ $r }}</td>
                            @for ($v = 1; $v <= $variaveis; $v++)
                                <td class="px-4 py-3">
                                <input
                                    value="{{ old('restricoes.' . $r . $v, $restricoesDados[$r][$v] ?? '') }}"
                                    type="number" step="any"
                                    name="restricoes[{{ $r }}][{{ $v }}]"
                                    placeholder="0"
                                    class="w-full px-4 py-2 text-center text-[#5C3A21] border border-[#A9745B] shadow-sm rounded-xl focus:ring-2 bg-[#FDF5E6] focus:ring-[#8B5E3C] placeholder-[#a3785c] focus:outline-none" />
                                </td>
                                @endfor
                                <td class="px-4 py-3">
                                    <select name="restricoes[{{ $r }}][sinal]"
                                        class="w-full px-4 py-2 border text-[#5C3A21] bg-[#FDF5E6] border-[#A9745B] shadow-sm rounded-xl focus:ring-2 focus:ring-[#8B5E3C] focus:outline-none">
                                        <option value="<=" @selected(old('restricoes.' . $r . 'sinal' , $restricoesDados[$r]['sinal'] ?? '<=' )=='<=' )>&le;</option>
                                        <option value="=" @selected(old('restricoes.' . $r . 'sinal' , $restricoesDados[$r]['sinal'] ?? '<=' )=='=' )>=</option>
                                        <option value=">=" @selected(old('restricoes.' . $r . 'sinal' , $restricoesDados[$r]['sinal'] ?? '<=' )=='>=' )>&ge;</option>
                                    </select>
                                </td>
                                <td class="px-4 py-3">
                                    <input
                                        value="{{ old('restricoes.' . $r . 'rhs', $restricoesDados[$r]['rhs'] ?? '') }}"
                                        type="number" step="any" name="restricoes[{{ $r }}][rhs]"
                                        placeholder="0"
                                        class="w-full px-4 py-2 text-center text-[#5C3A21] border border-[#A9745B] shadow-sm rounded-xl focus:ring-2 bg-[#FDF5E6] focus:ring-[#8B5E3C] placeholder-[#a3785c] focus:outline-none" />
                                </td>
                                </tr>
                                @endfor
                    </tbody>
                    <tr class="bg-white">
                        <td colspan="{{ $variaveis + 3 }}" class="pt-4"></td>
                    </tr>
                </table>
            </div>


            <!-- Escolha de método -->
            <section class="max-w-4xl mx-auto mt-12 mb-6 px-6">
                <h2 class="mb-4 text-xl font-semibold text-amber-950">Como deseja resolver o problema?</h2>
                <div class="flex flex-col gap-6 sm:flex-row">
                    <label
                        class="flex items-center w-full gap-3 p-4 transition-all border-[#5C3A21] bg-white border shadow-sm cursor-pointer rounded-2xl bg-gradient-to-br from-[#D8B28E] via-[#C69C6D] to-[#B88960] shadow-[#5C3A21]/40 hover:shadow-md hadow-inner sm:w-1/2">
                        <input type="radio" name="metodo" value="geometrica" required
                            class="w-5 h-5 accent-amber-800" />
                        <span class="text-lg text-white">Solução Geométrica</span>
                    </label>
                    <label
                        class="flex items-center w-full gap-3 p-4 transition-all border-[#5C3A21] bg-white border shadow-sm cursor-pointer rounded-2xl bg-gradient-to-br from-[#D8B28E] via-[#C69C6D] to-[#B88960] shadow-[#5C3A21]/40 hover:shadow-md hadow-inner sm:w-1/2">
                        <input type="radio" name="metodo" value="algebrica" required
                            class="w-5 h-5 accent-amber-800" />
                        <span class="text-lg text-white">Solução Algébrica</span>
                    </label>
                </div>
            </section>
            
            <div class="flex items-center justify-center mt-6">
                <input type="checkbox" id="integer_solution" name="integer_solution" value="1" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500">
                <label for="integer_solution" class="ml-2 text-lg font-medium text-gray-900">Encontrar Solução Inteira (Branch and Bound)</label>
            </div>

            <!-- Botão -->
            <div class="flex justify-center pt-4">
                <button type="submit"
                    class="inline-flex items-center justify-center px-8 py-4 text-xl font-semibold text-white rounded-full hover:scale-105 transition-all duration-300 ease-in-out bg-gradient-to-br from-[#8B5E3C] to-[#A9745B] border-2 border-[#5C3A21] shadow-inner">
                    Resolver →
                </button>
            </div>
        </form>
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