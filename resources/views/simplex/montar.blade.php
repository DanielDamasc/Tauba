<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Táuba</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;900&display=swap" rel="stylesheet" />
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }

        input[type="number"]::-webkit-inner-spin-button {
            appearance: none;
        }
    </style>
</head>

<body class="bg-gradient-to-br from-[#f0f4ff] via-[#e1ecf7] to-[#dce9f5] min-h-screen py-12 px-8">

    <a href="{{ route('simplex.dados') }}"
        class="absolute top-6 right-6 inline-flex items-center gap-2 px-5 py-2.5 text-sm font-semibold text-white bg-indigo-600 rounded-full shadow hover:bg-indigo-700 transition-all duration-300">
        ← Voltar para os dados
    </a>

    <div class="mx-auto max-w-7xl">
        <header class="mb-12">
            <h1 class="mb-2 text-5xl font-extrabold text-gray-900">Montar Problema</h1>
            <p class="text-xl text-gray-600">
                Informe os coeficientes da função objetivo e das restrições para a
                <span class="font-semibold text-indigo-600">
                    {{ $tipo === 'min' ? 'minimização' : 'maximização' }}
                </span>.
            </p>
        </header>

        <form action="{{ route('simplex.resolver') }}" method="POST" class="space-y-14">
            @csrf
            <input type="hidden" name="tipo" value="{{ $tipo }}">
            <input type="hidden" name="variaveis" value="{{ $variaveis }}">
            <input type="hidden" name="restricoes" value="{{ $restricoes }}">

            <!-- Tabela de entrada -->
            <div class="overflow-x-auto bg-white border border-gray-200 shadow-xl rounded-2xl">
                <table class="min-w-full text-lg text-center">
                    <thead class="text-gray-800 bg-indigo-50">
                        <tr>
                            <th class="px-6 py-4 text-left">Função</th>
                            @for ($v = 1; $v <= $variaveis; $v++) <th class="px-6 py-4">x{{ $v }}</th>
                                @endfor
                                <th class="px-6 py-4">Sinal</th>
                                <th class="px-6 py-4">RHS</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Linha Z -->
                        <tr class="border-b bg-indigo-50/40">
                            <td class="px-6 py-4 font-bold text-left text-indigo-700">Z</td>
                            @for ($v = 1; $v <= $variaveis; $v++) <td class="px-4 py-3">
                                <input type="number" step="any" name="z[{{ $v }}]" placeholder="0"
                                    class="w-full px-4 py-2 text-center border border-gray-300 shadow-sm rounded-xl focus:ring-2 focus:ring-indigo-300 focus:outline-none" />
                                </td>
                                @endfor
                                <td></td>
                                <td></td>
                        </tr>

                        <!-- Restrições -->
                        @for ($r = 1; $r <= $restricoes; $r++) <tr class="border-b hover:bg-gray-50">
                            <td class="px-6 py-4 font-semibold text-left text-gray-700">R{{ $r }}</td>
                            @for ($v = 1; $v <= $variaveis; $v++) <td class="px-4 py-3">
                                <input type="number" step="any" name="restricoes[{{ $r }}][{{ $v }}]" placeholder="0"
                                    class="w-full px-4 py-2 text-center border border-gray-300 shadow-sm rounded-xl focus:ring-2 focus:ring-indigo-300 focus:outline-none" />
                                </td>
                                @endfor
                                <td class="px-4 py-3">
                                    <select name="restricoes[{{ $r }}][sinal]"
                                        class="w-full px-4 py-2 border border-gray-300 shadow-sm rounded-xl focus:ring-2 focus:ring-indigo-300 focus:outline-none">
                                        <option value="<=">&le;</option>
                                        <option value="=">=</option>
                                        <option value=">=">&ge;</option>
                                    </select>
                                </td>
                                <td class="px-4 py-3">
                                    <input type="number" step="any" name="restricoes[{{ $r }}][rhs]" placeholder="0"
                                        class="w-full px-4 py-2 text-center border border-gray-300 shadow-sm rounded-xl focus:ring-2 focus:ring-indigo-300 focus:outline-none" />
                                </td>
                                </tr>
                                @endfor
                    </tbody>
                    <tr>
                        <td colspan="{{ $variaveis + 3 }}" class="pt-4"></td>
                    </tr>
                </table>
            </div>


            <!-- Escolha de método -->
            <section class="max-w-4xl mx-auto">
                <h2 class="mb-4 text-xl font-semibold text-gray-800">Como deseja resolver o problema?</h2>
                <div class="flex flex-col gap-6 sm:flex-row">
                    <label
                        class="flex items-center w-full gap-3 p-4 transition-all bg-white border border-gray-300 shadow-sm cursor-pointer rounded-2xl hover:shadow-md sm:w-1/2">
                        <input type="radio" name="metodo" value="geometrica" required
                            class="w-5 h-5 accent-indigo-600" />
                        <span class="text-lg text-gray-700">Solução Geométrica</span>
                    </label>
                    <label
                        class="flex items-center w-full gap-3 p-4 transition-all bg-white border border-gray-300 shadow-sm cursor-pointer rounded-2xl hover:shadow-md sm:w-1/2">
                        <input type="radio" name="metodo" value="algebrica" required
                            class="w-5 h-5 accent-indigo-600" />
                        <span class="text-lg text-gray-700">Solução Algébrica</span>
                    </label>
                </div>
            </section>

            <!-- Botão -->
            <div class="flex justify-center pt-4">
                <button type="submit"
                    class="inline-flex items-center px-10 py-4 text-lg font-semibold text-white transition-all duration-300 bg-indigo-600 rounded-full shadow-md hover:bg-indigo-700 hover:scale-105">
                    Resolver →
                </button>
            </div>
        </form>
    </div>

</body>

</html>