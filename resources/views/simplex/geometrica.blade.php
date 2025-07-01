<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Táuba</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;900&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <style>
        body {font-family: 'Inter', sans-serif;}
    </style>
</head>

<body class="bg-gradient-to-br from-[#C69C6D] via-[#ffe4c6] to-[#B88960] min-h-screen py-12 px-6">
    <!-- Botão Voltar -->
    <div class="absolute top-6 left-6 flex flex-col gap-2">
        <a href="{{ route('simplex.index') }}" class="duration-300 hover:scale-110">
            <img class="w-8" src="{{ asset('assets/home.svg') }}" alt="Voltar para a página inicial" title="Voltar para a página inicial">
        </a>
        <a href="{{ route('simplex.montar') }}" class="duration-300 hover:scale-110">
            <img class="w-8" src="{{ asset('assets/back.svg') }}" alt="Voltar para a etapa anterior" title="Voltar para a etapa anterior">
        </a>
    </div>

    <div class="mx-auto space-y-16 max-w-7xl">

        {{-- Cabeçalho --}}
        <header class="text-center">
            <h1 class="mx-12 mb-2 text-5xl font-extrabold tracking-tight text-[#5C3A21]">Resultado da Solução Geométrica</h1>
            <p class="mb-12 text-lg text-amber-950 sm:text-xl">Abaixo está o gráfico</p>
        </header>

        {{-- Imagem do Gráfico --}}
        <div class="flex justify-center overflow-hidden bg-white border border-orange-100 shadow-lg rounded-2xl">
            <img src="{{asset('graficos/' . $nome)}}" alt="Grafico do Problema">
        </div>

        {{-- Resultado Final --}}
        @if(isset($solucao))
            <div
                class="max-w-2xl p-8 mx-auto mt-16 text-center bg-amber-50 border-l-8 border-amber-800 shadow-xl rounded-2xl">
                <div class="flex items-center justify-center gap-3 mb-4 text-4xl font-extrabold text-amber-800">
                    Solução ótima encontrada
                </div>
                <p class="mt-2 text-3xl font-bold text-amber-900">
                    Z = <span class="text-amber-800">{{$solucao}}</span>
                </p>
            </div>
        @endif

        {{-- Botão para salvar um problema no filesystem. --}}
        <div class="flex justify-center">
            <a onclick="toastr.success('Sucesso: problema baixado!')" href="{{ route('simplex.download') }}"
                class="px-6 py-3 text-lg font-semibold text-[#5C3A21] bg-white/30 border border-white/60 rounded-full shadow backdrop-blur hover:bg-white/40 transition-all duration-300">
                📂 <span>Salvar problema</span>
            </a>
        </div>


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
