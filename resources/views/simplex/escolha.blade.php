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
    </style>
</head>

<body
    class="relative flex flex-col items-center justify-center min-h-screen bg-gradient-to-br from-[#C69C6D] via-[#ffe4c6] to-[#B88960] overflow-hidden">

    <main class="w-full max-w-4xl text-center">
        <h1 class="mb-6 text-6xl sm:text-7xl font-extrabold tracking-tight text-[#5C3A21]">Qual tipo de problema você quer resolver?
        </h1>
        <p class="mb-12 text-lg text-amber-950 sm:text-xl">Escolha o tipo de função objetivo para começar:</p>

        <div class="grid gap-6 sm:grid-cols-2">
            <!-- Maximização -->
            <a href="{{ route('simplex.dados', ['tipo' => 'max']) }}"
                class="block p-8 transition-all rounded-2xl shadow-inner shadow-[#5C3A21]/40 border-2 border-[#5C3A21] bg-gradient-to-br from-[#D8B28E] via-[#C69C6D] to-[#B88960] hover:scale-105">
                <h2 class="mb-2 text-2xl font-bold text-amber-50">Maximização</h2>
                <p class="text-amber-900">Quando você quer obter o maior valor possível (ex: lucro, produção).</p>
            </a>

            <!-- Minimização -->
            <a href="{{ route('simplex.dados', ['tipo' => 'min']) }}"
                class="block p-8 transition-all rounded-2xl shadow-inner shadow-[#5C3A21]/40 border-2 border-[#5C3A21] bg-gradient-to-br from-[#D8B28E] via-[#C69C6D] to-[#B88960] hover:scale-105">
                <h2 class="mb-2 text-2xl font-bold text-amber-50">Minimização</h2>
                <p class="text-amber-900">Quando o objetivo é reduzir custos, desperdícios ou tempo.</p>
            </a>
        </div>
    </main>

</body>

</html>