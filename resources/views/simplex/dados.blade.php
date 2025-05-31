<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Táuba</title>
    <script src="https://kit.fontawesome.com/cc9f72a45c.js" crossorigin="anonymous"></script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
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

    <div class="absolute top-6 left-6 flex flex-col gap-2">
        <a href="{{ route('simplex.index') }}" class="duration-300 hover:scale-110">
            <i class="fa-solid fa-house bg-[linear-gradient(224.36deg,_#995026_27.29%,_#5C3A21_62.58%)] bg-clip-text text-transparent text-2xl"></i>
        </a>
        <a href="{{ route('simplex.escolha') }}" class="duration-300 hover:scale-110">
            <i class="fa-solid fa-circle-left bg-[linear-gradient(224.36deg,_#995026_27.29%,_#5C3A21_62.58%)] bg-clip-text text-transparent text-2xl"></i>
        </a>
    </div>

    <main class="mx-6 max-w-4xl text-center">
        <h1 class="mb-6 text-6xl sm:text-7xl font-extrabold tracking-tight text-[#5C3A21]">
            Estrutura do Problema
        </h1>
        <p class="mb-12 text-lg text-amber-950 sm:text-xl">
            Informe quantas variáveis e restrições você deseja utilizar para a
            <span
                class="font-semibold text-amber-800">{{ request('tipo') === 'min' ? 'minimização' : 'maximização' }}</span>.
        </p>

        <form action="{{ route('simplex.montar') }}" method="GET"
            class="grid items-start gap-6 text-left sm:grid-cols-2 mx-6">

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