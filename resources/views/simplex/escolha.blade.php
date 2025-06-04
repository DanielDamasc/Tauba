<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Táuba</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
    </style>
</head>

<body
    class="relative flex flex-col items-center justify-center min-h-screen bg-gradient-to-br from-[#C69C6D] via-[#ffe4c6] to-[#B88960] overflow-hidden">
    
    <div class="absolute top-6 left-6 flex flex-col gap-2">
        <a href="{{ route('simplex.index') }}" class="duration-300 hover:scale-110">
            <img class="w-8" src="{{ asset('assets/home.svg') }}" alt="Voltar para a página inicial" title="Voltar para a página inicial">
        </a>
    </div>

    <main class="w-full max-w-4xl text-center">
        <h1 class="mb-6 text-6xl sm:text-7xl font-extrabold tracking-tight text-[#5C3A21]">Qual tipo de problema você quer resolver?
        </h1>
        <p class="mb-12 text-lg text-amber-950 sm:text-xl">Escolha o tipo de função objetivo para começar:</p>

        <div class="grid gap-6 sm:grid-cols-2 mx-6">
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
        
        {{-- Botão para recuperar um problema existente do filesystem. --}}
        <form action="{{ route('simplex.importar') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="mt-8">
                <input type="file" id="existing-problem" name="existing-problem" class="hidden"
                    onchange="this.form.submit()">
                <button type="button" onclick="document.getElementById('existing-problem').click()"
                    class="px-6 py-3 text-lg font-semibold text-[#5C3A21] bg-white/30 border border-white/60 rounded-full shadow backdrop-blur hover:bg-white/50 transition-all duration-300">
                    📂 <span>Selecionar problema existente</span>
                </button>
            </div>
        </form>
    </main>

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