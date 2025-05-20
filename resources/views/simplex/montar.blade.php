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

<body class="relative flex flex-col items-center justify-center min-h-screen bg-gradient-to-br from-[#C69C6D] via-[#ffe4c6] to-[#B88960]">

    <a href="{{ route('simplex.dados') }}"
        class="absolute top-6 right-6 inline-flex items-center gap-2 px-5 py-2.5 text-sm font-semibold text-white 
          rounded-full shadow-md transition-all duration-300 
          bg-gradient-to-br from-[#A9745B] to-[#8B5E3C] border border-[#5C3A21] hover:brightness-110">
        ← Voltar para os dados
    </a>

    <div class="mx-auto max-w-7xl">
        <header class="mb-12">
            <h1 class="mb-2 text-5xl font-extrabold tracking-tight text-[#5C3A21]">Montar Problema</h1>
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
            <div class="overflow-x-auto bg-white border border-orange-100 shadow-xl rounded-2xl">
                <table class="min-w-full text-lg text-center">
                    <thead class="text-amber-900 bg-orange-100">
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
                        <tr class="border-b bg-orange-50">
                            <td class="px-6 py-4 font-bold text-left text-amber-800">Z</td>
                            @for ($v = 1; $v <= $variaveis; $v++) <td class="px-4 py-3">
                                <input type="number" step="any" name="z[{{ $v }}]" placeholder="0"
                                    class="w-full px-4 py-2 text-center text-[#5C3A21] border border-[#A9745B] shadow-sm rounded-xl focus:ring-2 bg-[#FDF5E6] focus:ring-[#8B5E3C] placeholder-[#a3785c] focus:outline-none" />
                                </td>
                                @endfor
                                <td></td>
                                <td></td>
                        </tr>

                        <!-- Restrições -->
                        @for ($r = 1; $r <= $restricoes; $r++) <tr class="border-b bg-white hover:bg-orange-100">
                            <td class="px-6 py-4 font-semibold text-left text-amber-900">R{{ $r }}</td>
                            @for ($v = 1; $v <= $variaveis; $v++) <td class="px-4 py-3">
                                <input type="number" step="any" name="restricoes[{{ $r }}][{{ $v }}]" placeholder="0"
                                    class="w-full px-4 py-2 text-center text-[#5C3A21] border border-[#A9745B] shadow-sm rounded-xl focus:ring-2 bg-[#FDF5E6] focus:ring-[#8B5E3C] placeholder-[#a3785c] focus:outline-none" />
                                </td>
                                @endfor
                                <td class="px-4 py-3">
                                    <select name="restricoes[{{ $r }}][sinal]"
                                        class="w-full px-4 py-2 border text-[#5C3A21] bg-[#FDF5E6] border-[#A9745B] shadow-sm rounded-xl focus:ring-2 focus:ring-[#8B5E3C] focus:outline-none">
                                        <option value="<=">&le;</option>
                                        <option value="=">=</option>
                                        <option value=">=">&ge;</option>
                                    </select>
                                </td>
                                <td class="px-4 py-3">
                                    <input type="number" step="any" name="restricoes[{{ $r }}][rhs]" placeholder="0"
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
            <section class="max-w-4xl mx-auto mt-12 mb-6">
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

            <!-- Botão -->
            <div class="flex justify-center pt-4">
                <button type="submit"
                    class="inline-flex items-center justify-center px-8 py-4 text-xl font-semibold text-white rounded-full hover:scale-105 transition-all duration-300 ease-in-out bg-gradient-to-br from-[#8B5E3C] to-[#A9745B] border-2 border-[#5C3A21] shadow-inner">
                    Resolver →
                </button>
            </div>
        </form>
    </div>

</body>

</html>