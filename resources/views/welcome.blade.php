<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Táuba</title>

    <!-- Font -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;900&display=swap" rel="stylesheet">


    <!-- Vite -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- TailwindCDN fallback for now -->
    <script src="https://cdn.tailwindcss.com"></script>

    <style>
        body {
            font-family: 'Inter', sans-serif;
        }

        .animate-float {
            animation: float 6s ease-in-out infinite;
        }

        @keyframes float {

            0%,
            100% {
                transform: translateY(0);
            }

            50% {
                transform: translateY(-20px);
            }
        }
    </style>
</head>

<body
    class="relative flex flex-col items-center justify-center min-h-screen bg-gradient-to-br from-[#f0f4ff] via-[#e1ecf7] to-[#dce9f5] overflow-hidden">



    <!-- Conteúdo central -->
    <main class="z-10 px-6 text-center">
        <h1 class="mb-6 text-6xl font-extrabold tracking-tight text-gray-900 sm:text-7xl drop-shadow">
            Táuba
        </h1>

        <p class="max-w-2xl mx-auto mb-12 text-lg leading-relaxed text-gray-600 sm:text-2xl">
            Resolvendo problemas de <span class="font-semibold text-indigo-600">Programação
                Linear</span>.<br>

        </p>

        <!-- Botão -->
        <a href="{{ route('simplex.escolha') }}"
            class="inline-flex items-center justify-center px-8 py-4 text-xl font-semibold text-white transition-all duration-300 ease-in-out bg-indigo-600 rounded-full shadow-lg hover:bg-indigo-700 hover:scale-105">
            Começar agora →
        </a>
    </main>

    <!-- Rodapé -->
    <footer class="absolute z-10 w-full text-sm text-center text-gray-400 bottom-4">
        &copy; {{ date('Y') }} Táuba.
    </footer>
</body>

</html>