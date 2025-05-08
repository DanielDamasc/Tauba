<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Táuba</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;900&display=swap" rel="stylesheet">
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
    class="bg-gradient-to-br from-[#f0f4ff] via-[#e1ecf7] to-[#dce9f5] min-h-screen flex items-center justify-center px-6 py-12">

    <a href="{{ route('simplex.escolha') }}"
        class="absolute top-6 right-6 inline-flex items-center gap-2 px-5 py-2.5 text-sm font-semibold text-white bg-indigo-600 rounded-full shadow hover:bg-indigo-700 transition-all duration-300">
        ← Voltar para a escolha
    </a>

    <main class="w-full max-w-4xl text-center">
        <h1 class="mb-6 text-4xl font-extrabold text-gray-900 sm:text-5xl">
            Estrutura do Problema
        </h1>
        <p class="mb-12 text-lg text-gray-600 sm:text-xl">
            Informe quantas variáveis e restrições você deseja utilizar para a
            <span class="font-semibold text-indigo-600">
                {{ request('tipo') === 'min' ? 'minimização' : 'maximização' }}
            </span>.
        </p>

        <form action="{{ route('simplex.montar') }}" method="GET"
            class="grid items-start gap-6 text-left sm:grid-cols-2">

            <input type="hidden" name="tipo" value="{{ request('tipo') }}">

            <!-- Campo Variáveis -->
            <div class="p-8 transition-all bg-white border border-gray-200 shadow-md rounded-2xl hover:shadow-lg">
                <label for="variaveis" class="block mb-3 text-lg font-semibold text-gray-700">
                    Número de Variáveis
                </label>
                <input type="number" id="variaveis" name="variaveis" min="1" max="10" required placeholder="Ex: 3"
                    class="w-full px-6 py-4 text-xl text-gray-800 placeholder-gray-400 transition border border-gray-300 shadow-sm rounded-xl focus:ring-2 focus:ring-indigo-300 focus:outline-none" />
            </div>

            <!-- Campo Restrições -->
            <div class="p-8 transition-all bg-white border border-gray-200 shadow-md rounded-2xl hover:shadow-lg">
                <label for="restricoes" class="block mb-3 text-lg font-semibold text-gray-700">
                    Número de Restrições
                </label>
                <input type="number" id="restricoes" name="restricoes" min="1" max="10" required placeholder="Ex: 4"
                    class="w-full px-6 py-4 text-xl text-gray-800 placeholder-gray-400 transition border border-gray-300 shadow-sm rounded-xl focus:ring-2 focus:ring-indigo-300 focus:outline-none" />
            </div>

            <!-- Botão centralizado -->
            <div class="flex justify-center mt-8 sm:col-span-2">
                <button type="submit"
                    class="inline-flex items-center justify-center px-10 py-4 text-lg font-semibold text-white transition-all bg-indigo-600 rounded-full shadow-md hover:bg-indigo-700 hover:shadow-lg hover:scale-105">
                    Continuar →
                </button>
            </div>
        </form>
    </main>

</body>

</html>