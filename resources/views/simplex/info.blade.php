<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Táuba</title>

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
        <a href="{{ route('simplex.index') }}" class="duration-300 hover:scale-110">
            <img class="w-8" src="{{ asset('assets/home.svg') }}" alt="Voltar para a página inicial" title="Voltar para a página inicial">
        </a>
    </div>

    <!-- Conteúdo central -->
    <main class="z-10 px-6 text-center">
        <h1 class="mx-12 mb-6 text-6xl sm:text-7xl font-extrabold tracking-tight text-[#5C3A21]">
            Táuba
        </h1>

        <p class="max-w-2xl mx-auto mb-12 text-lg leading-relaxed text-amber-950 sm:text-2xl">
            <span class="font-semibold text-amber-800">Tutorial: </span>Como usar?<br>

        </p>

    <!-- Tutorial -->
    <section class="max-w-3xl mx-auto text-left bg-white/80 backdrop-blur-sm rounded-2xl p-6 shadow-md text-amber-900">
        <ol class="space-y-4 list-decimal list-inside">
            <li>
                <span class="font-semibold">Escolha o tipo de problema:</span> maximização ou minimização.
                <p class="text-sm text-amber-700 italic">Dica: você também pode importar um problema já salvo (JSON).</p>
            </li>
            <li>
                <span class="font-semibold">Indique o número de variáveis e restrições:</span> numa escala de 1 a 10.
            </li>
            <li>
                <span class="font-semibold">Preencha os dados:</span> insira os coeficientes da função objetivo e das restrições.
            </li>
            <li>
                <span class="font-semibold">Escolha a forma de sulucionar:</span> método gráfico, algébrico ou branch and bound.
            </li>
            <li>
                <span class="font-semibold">Clique em "Resolver":</span> o sistema vai calcular o passo a passo do método Simplex.
            </li>
            <li>
                <span class="font-semibold">Pronto:</span> o Táuba mostrará a solução ótima com o passo a passo da solução.
                <p class="text-sm text-amber-700 italic">Dica: você pode salvar o problema em JSON para usá-lo novamente caso queira.</p>
            </li>
        </ol>
    </section>
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
