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
    class="bg-gradient-to-br from-[#f0f4ff] via-[#e1ecf7] to-[#dce9f5] min-h-screen flex items-center justify-center px-6 py-12">

    <main class="w-full max-w-4xl text-center">
        <h1 class="mb-6 text-4xl font-extrabold text-gray-900 sm:text-5xl">Qual tipo de problema você quer resolver?
        </h1>
        <p class="mb-12 text-lg text-gray-600 sm:text-xl">Escolha o tipo de função objetivo para começar:</p>

        <div class="grid gap-6 sm:grid-cols-2">
            <!-- Maximização -->
            <a href="{{ route('simplex.dados', ['tipo' => 'max']) }}"
                class="block p-8 transition-all bg-white border border-gray-200 shadow-md rounded-2xl hover:bg-indigo-50 hover:shadow-xl">
                <h2 class="mb-2 text-2xl font-bold text-indigo-600">Maximização</h2>
                <p class="text-gray-500">Quando você quer obter o maior valor possível (ex: lucro, produção).</p>
            </a>

            <!-- Minimização -->
            <a href="{{ route('simplex.dados', ['tipo' => 'min']) }}"
                class="block p-8 transition-all bg-white border border-gray-200 shadow-md rounded-2xl hover:bg-indigo-50 hover:shadow-xl">
                <h2 class="mb-2 text-2xl font-bold text-purple-600">Minimização</h2>
                <p class="text-gray-500">Quando o objetivo é reduzir custos, desperdícios ou tempo.</p>
            </a>
        </div>
    </main>

</body>

</html>