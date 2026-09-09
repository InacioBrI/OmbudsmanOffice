<!DOCTYPE html>
<html lang="pt-BR" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('titulo', 'Ouvidoria BA')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-100 text-slate-800 antialiased font-sans min-h-screen flex flex-col">

    @hasSection('hero')
        @yield('hero')
    @else
        <header class="bg-[#0b1f3a] text-white">
            <div class="max-w-6xl mx-auto px-6 py-4 flex items-center gap-3">
                <div class="w-9 h-9 rounded-md bg-white flex items-center justify-center">
                    <svg class="w-5 h-5 text-[#0b1f3a]" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 3l7 3v5c0 4.5-3 8-7 10-4-2-7-5.5-7-10V6l7-3z" />
                    </svg>
                </div>
                <a href="{{ route('home') }}" class="font-semibold">Ouvidoria BA</a>
                <nav class="ml-auto text-sm">
                    <a href="{{ route('manifestacoes.acompanhar') }}" class="text-slate-200 hover:text-white">Acompanhar manifestação</a>
                </nav>
            </div>
        </header>
    @endif

    <main class="flex-1">
        @yield('conteudo')
    </main>

    <footer class="bg-[#0b1f3a] text-white">
        <div class="max-w-6xl mx-auto px-6 py-8 grid grid-cols-2 md:grid-cols-4 gap-6 text-[11px] leading-relaxed">
            @foreach ([
                ['name' => 'Campus Paraíso:', 'addr' => 'Rua Estela, 64 - SP'],
                ['name' => 'Campus Vila Mariana:', 'addr' => 'Rua Dr. Álvaro Alvim, 90 - SP'],
                ['name' => 'Campus Shop. Cidade Jardim:', 'addr' => 'Av. Magalhães de Castro, 12.000 - SP'],
                ['name' => 'Campus Sorocaba-Votorantim:', 'addr' => 'Av. Gisele Constantino, nº1850 - Iguatemi Business, Votorantim'],
            ] as $c)
                <div class="flex gap-2">
                    <svg class="w-3 h-3 mt-0.5 text-amber-500 shrink-0" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C8 2 5 5 5 9c0 5 7 13 7 13s7-8 7-13c0-4-3-7-7-7zm0 9.5A2.5 2.5 0 1112 6a2.5 2.5 0 010 5.5z"/></svg>
                    <div>
                        <p class="font-semibold text-amber-500">{{ $c['name'] }}</p>
                        <p class="text-slate-300">{{ $c['addr'] }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    </footer>

    <a href="#" class="fixed bottom-5 left-5 w-10 h-10 rounded-full bg-amber-500 hover:bg-amber-600 transition-colors flex items-center justify-center text-white shadow-lg">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 19V5M5 12l7-7 7 7"/></svg>
    </a>

</body>
</html>
