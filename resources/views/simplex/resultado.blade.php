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
    </style>
</head>

<body class="bg-gradient-to-br from-[#f0f4ff] via-[#e1ecf7] to-[#dce9f5] min-h-screen py-12 px-6">

    <!-- Botão Voltar -->
    <a href="{{ url()->previous() }}"
        class="absolute top-6 right-6 inline-flex items-center gap-2 px-5 py-2.5 text-sm font-semibold text-white bg-indigo-600 rounded-full shadow hover:bg-indigo-700 transition-all duration-300">
        ← Voltar para a montagem
    </a>

    <div class="mx-auto space-y-16 max-w-7xl">

        {{-- Cabeçalho --}}
        <header class="text-center">
            <h1 class="mb-2 text-5xl font-extrabold text-gray-900">Resultado da Solução Algébrica</h1>
            <p class="text-lg text-gray-600">Abaixo estão as iterações do método Simplex</p>
        </header>

        {{-- Iterações (Exemplo Estático) --}}
        @for ($i = 1; $i <= 3; $i++) <div class="overflow-hidden bg-white border border-gray-200 shadow-lg rounded-2xl">
            <div class="px-6 py-4 text-lg font-semibold text-gray-800 bg-indigo-100">
                Iteração {{ $i }} {{ $i === 2 ? '- Encontrando Pivô' : ($i === 3 ? '- Solução Ótima' : '') }}
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full text-lg text-center">
                    <thead class="text-gray-700 bg-indigo-50">
                        <tr>
                            <th class="p-4 text-left">Função</th>
                            <th class="p-4">x1</th>
                            <th class="p-4">x2</th>
                            <th class="p-4">sx3</th>
                            <th class="p-4">sx4</th>
                            <th class="p-4">Solução</th>
                        </tr>
                    </thead>
                    <tbody>
                        {{-- Linha Z --}}
                        <tr class="border-b">
                            <td class="p-4 font-semibold text-left text-indigo-700">Z (Min)</td>
                            <td class="p-3 {{ $i === 2 ? 'cell-red' : '' }}">-2</td>
                            <td class="p-3">-2</td>
                            <td class="p-3">0</td>
                            <td class="p-3">0</td>
                            <td class="p-3">0</td>
                        </tr>

                        {{-- Linha 2 --}}
                        <tr class="border-b">
                            <td class="p-4 font-medium text-left text-gray-700">s2</td>
                            <td class="p-3 {{ $i === 2 ? 'cell-red' : '' }}">2</td>
                            <td class="p-3">2</td>
                            <td class="p-3">1</td>
                            <td class="p-3">0</td>
                            <td class="p-3">2</td>
                        </tr>

                        {{-- Linha 3 --}}
                        <tr class="border-b">
                            <td class="p-4 font-medium text-left text-gray-700">s3</td>
                            <td class="p-3 {{ $i === 2 ? 'cell-green cell-pivot' : '' }}">-2</td>
                            <td class="p-3 {{ $i === 2 ? 'cell-red' : '' }}">-2</td>
                            <td class="p-3">0</td>
                            <td class="p-3">-1</td>
                            <td class="p-3">-2</td>
                        </tr>
                    </tbody>
                </table>
            </div>
    </div>
    @endfor

    {{-- Resultado Final --}}
    <div class="max-w-2xl p-8 mx-auto mt-16 text-center bg-white border-l-8 border-indigo-600 shadow-xl rounded-2xl">
        <div class="flex items-center justify-center gap-3 mb-4 text-4xl font-extrabold text-indigo-700">
            Solução ótima encontrada
        </div>
        <p class="mt-2 text-3xl font-bold text-gray-800">
            Z = <span class="text-indigo-700">8</span>
        </p>
        <p class="mt-4 text-xl text-gray-600">
            x₁ = <span class="font-semibold text-indigo-600">2</span>,
            x₂ = <span class="font-semibold text-indigo-600">0</span>
        </p>
    </div>
    </div>

</body>

</html>