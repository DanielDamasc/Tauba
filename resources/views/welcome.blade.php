<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Táuba</title>
    <script src="https://kit.fontawesome.com/cc9f72a45c.js" crossorigin="anonymous"></script>

    <!-- Font -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">


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

<body class="relative flex flex-col items-center justify-center min-h-screen bg-gradient-to-br from-[#C69C6D] via-[#ffe4c6] to-[#B88960] overflow-hidden">

    <div class="absolute top-6 left-6 flex flex-col gap-2">
        <a href="{{ route('simplex.info') }}" class="duration-300 hover:scale-110">
            <img class="w-8" src="{{ asset('assets/info.svg') }}" alt="Saiba mais" title="Saiba mais">
        </a>
    </div>
    <!-- Conteúdo central -->
    <main class="z-10 px-6 text-center">
        <h1 class="mb-6 text-6xl sm:text-7xl font-extrabold tracking-tight text-[#5C3A21]">
            Táuba
        </h1>

        <p class="max-w-2xl mx-auto mb-12 text-lg leading-relaxed text-amber-950 sm:text-2xl">
            Resolvendo problemas de <span class="font-semibold text-amber-800">Programação
                Linear</span>.<br>

        </p>

        <!-- Botão -->
        <a href="{{ route('simplex.escolha') }}"
            class="inline-flex items-center justify-center px-8 py-4 text-xl font-semibold text-white rounded-full hover:scale-105 transition-all duration-300 ease-in-out bg-gradient-to-br from-[#8B5E3C] to-[#A9745B] border-2 border-[#5C3A21] shadow-inner">
            Começar agora →
        </a>
    </main>

    <!-- Rodapé -->
    <footer class="absolute z-10 w-full text-sm text-center text-amber-700 bottom-4">
        &copy; {{ date('Y') }} Táuba.
    </footer>


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