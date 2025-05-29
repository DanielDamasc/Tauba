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
    class="relative flex flex-col items-center justify-center min-h-screen bg-gradient-to-br from-[#C69C6D] via-[#ffe4c6] to-[#B88960]">

    <a href="{{ route('simplex.escolha') }}"
        class="absolute top-6 right-6 inline-flex items-center gap-2 px-5 py-2.5 text-sm font-semibold text-white 
          rounded-full shadow-md transition-all duration-300 
          bg-gradient-to-br from-[#A9745B] to-[#8B5E3C] border border-[#5C3A21] hover:brightness-110">
        ← Voltar para a escolha
    </a>

    <main class="w-full max-w-4xl text-center">
        <h1 class="mb-6 text-6xl sm:text-7xl font-extrabold tracking-tight text-[#5C3A21]">
            Estrutura do Problema
        </h1>
        <p class="mb-12 text-lg text-amber-950 sm:text-xl">
            Informe quantas variáveis e restrições você deseja utilizar para a
            <span
                class="font-semibold text-amber-800">{{ request('tipo') === 'min' ? 'minimização' : 'maximização' }}</span>.
        </p>

        <form action="{{ route('simplex.montar') }}" method="GET"
            class="grid items-start gap-6 text-left sm:grid-cols-2">

            <input type="hidden" name="tipo" value="{{ request('tipo') }}">

            <!-- Campo Variáveis -->
            <div
                class="p-8 transition-all rounded-2xl shadow-inner shadow-[#5C3A21]/40 border-2 border-[#5C3A21] bg-gradient-to-br from-[#D8B28E] via-[#C69C6D] to-[#B88960]">
                <label for="variaveis" class="block mb-3 text-lg font-semibold text-[#5C3A21]">
                    Número de Variáveis
                </label>
                <input type="number" id="variaveis" name="variaveis" min="1" max="10" required
                    placeholder="Ex: 3"
                    class="w-full px-6 py-4 text-xl text-[#5C3A21] placeholder-[#a3785c] bg-[#FDF5E6] border border-[#A9745B] rounded-xl shadow-sm focus:ring-2 focus:ring-[#8B5E3C] focus:outline-none" />
            </div>

            <!-- Campo Restrições -->
            <div
                class="p-8 transition-all rounded-2xl shadow-inner shadow-[#5C3A21]/40 border-2 border-[#5C3A21] bg-gradient-to-br from-[#D8B28E] via-[#C69C6D] to-[#B88960]">
                <label for="restricoes" class="block mb-3 text-lg font-semibold text-[#5C3A21]">
                    Número de Restrições
                </label>
                <input type="number" id="restricoes" name="restricoes" min="1" max="10" required
                    placeholder="Ex: 4"
                    class="w-full px-6 py-4 text-xl text-[#5C3A21] placeholder-[#a3785c] bg-[#FDF5E6] border border-[#A9745B] rounded-xl shadow-sm focus:ring-2 focus:ring-[#8B5E3C] focus:outline-none" />
            </div>


            <!-- Botão centralizado -->
            <div class="flex justify-center mt-8 sm:col-span-2">
                <button type="submit"
                    class="inline-flex items-center justify-center px-8 py-4 text-xl font-semibold text-white rounded-full hover:scale-105 transition-all duration-300 ease-in-out bg-gradient-to-br from-[#8B5E3C] to-[#A9745B] border-2 border-[#5C3A21] shadow-inner">
                    Continuar →
                </button>
            </div>
        </form>

        {{-- Botão para recuperar um problema existente do filesystem. --}}
        <form action="{{ route('simplex.importar') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="mt-8">
                <input type="file" id="existing-problem" name="existing-problem" class="hidden"
                    onchange="this.form.submit()">
                <button type="button" onclick="document.getElementById('existing-problem').click()"
                    class="px-6 py-3 text-lg font-semibold text-[#5C3A21] bg-white/30 border border-white/60 rounded-full shadow backdrop-blur hover:bg-white/40 transition-all duration-300">
                    📂 <span>Selecionar problema existente</span>
                </button>
            </div>
        </form>
    </main>

</body>

</html>
