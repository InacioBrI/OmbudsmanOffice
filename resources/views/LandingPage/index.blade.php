@extends('layouts.app')

@section('titulo', 'Ouvidoria BA')

@section('hero')
    <header class="bg-[#0b1f3a] text-white">
        <div class="max-w-4xl mx-auto px-6 py-24 text-center">
            <h1 class="text-5xl md:text-6xl font-extrabold text-amber-500 tracking-tight">
                Ouvidoria BA
            </h1>
            <p class="mt-5 max-w-xl mx-auto text-sm md:text-base text-slate-200 leading-relaxed">
                Canal oficial para registro de elogios, denúncias, reclamações e solicitações,
                com garantia de sigilo e transparência.
            </p>
            <a href="{{ route('manifestacoes.create') }}"
               class="mt-8 inline-block bg-amber-500 hover:bg-amber-600 transition-colors text-white font-semibold text-sm px-8 py-3 rounded-md shadow">
                Registrar Manifestação
            </a>
        </div>
    </header>
@endsection

@section('conteudo')
    <div class="bg-white">
    {{-- ===================== BRAND BAR ===================== --}}
    <div class="max-w-6xl mx-auto px-6 pt-8 flex items-center gap-3">
        <div class="w-9 h-9 rounded-full border-2 border-slate-700 flex items-center justify-center">
            <svg class="w-5 h-5 text-slate-700" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 3l7 3v5c0 4.5-3 8-7 10-4-2-7-5.5-7-10V6l7-3z" />
            </svg>
        </div>
        <span class="font-semibold text-slate-800">Ouvidoria BA</span>
    </div>

    {{-- ===================== SIGILO NOTICE ===================== --}}
    <section class="max-w-6xl mx-auto px-6 pt-8">
        <div class="rounded-xl border border-indigo-200 bg-indigo-50/60 px-6 py-5">
            <div class="flex items-center gap-2 text-indigo-900 font-semibold">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 3l7 3v5c0 4.5-3 8-7 10-4-2-7-5.5-7-10V6l7-3z" />
                </svg>
                Garantia de sigilo
            </div>
            <p class="mt-2 text-sm text-slate-600 leading-relaxed">
                Todas as informações fornecidas são tratadas com absoluta confidencialidade.
                Manifestações são permitidas. Este sistema não é destinado à coleta de dados
                pessoais sensíveis ou informações de identificação pessoal (PII).
            </p>
        </div>
    </section>

    {{-- ===================== PRINCÍPIOS ===================== --}}
    <section class="max-w-6xl mx-auto px-6 py-16">
        <h2 class="text-center text-2xl font-semibold text-slate-800">Nossos Princípios</h2>

        <div class="mt-10 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            @foreach ([
                ['title' => 'Transparência', 'desc' => 'Processos claros e acessíveis a todos', 'color' => 'border-blue-300', 'icon' => 'shield'],
                ['title' => 'Sigilo', 'desc' => 'Proteção total das informações fornecidas', 'color' => 'border-emerald-300', 'icon' => 'lock'],
                ['title' => 'Legalidade', 'desc' => 'Conformidade com as normas vigentes', 'color' => 'border-blue-300', 'icon' => 'check'],
                ['title' => 'Eficiência', 'desc' => 'Respostas ágeis e efetivas', 'color' => 'border-emerald-300', 'icon' => 'clock'],
            ] as $p)
                <div class="rounded-lg border {{ $p['color'] }} bg-white px-6 py-10 text-center hover:shadow-md transition-shadow">
                    <div class="flex justify-center text-slate-700">
                        @switch($p['icon'])
                            @case('shield')
                                <svg class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3l7 3v5c0 4.5-3 8-7 10-4-2-7-5.5-7-10V6l7-3z"/></svg>
                                @break
                            @case('lock')
                                <svg class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24"><rect x="5" y="11" width="14" height="9" rx="2"/><path stroke-linecap="round" d="M8 11V7a4 4 0 118 0v4"/></svg>
                                @break
                            @case('check')
                                <svg class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24"><rect x="4" y="4" width="16" height="16" rx="3"/><path stroke-linecap="round" stroke-linejoin="round" d="M8.5 12.5l2.5 2.5 4.5-5"/></svg>
                                @break
                            @case('clock')
                                <svg class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24"><circle cx="12" cy="12" r="8"/><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l2.5 2.5"/></svg>
                                @break
                        @endswitch
                    </div>
                    <h3 class="mt-5 text-lg font-medium text-slate-800">{{ $p['title'] }}</h3>
                    <p class="mt-2 text-xs text-slate-500">{{ $p['desc'] }}</p>
                </div>
            @endforeach
        </div>
    </section>

    {{-- ===================== TIPOS DE MANIFESTAÇÃO ===================== --}}
    <section class="max-w-4xl mx-auto px-6 pb-20">
        <h2 class="text-center text-2xl font-semibold text-slate-800">Tipos de Manifestação</h2>

        <div class="mt-10 grid grid-cols-1 sm:grid-cols-2 gap-6">
            @foreach ([
                ['title' => 'Elogio', 'desc' => 'Reconhecimento de boas práticas, serviços ou atendimentos prestados'],
                ['title' => 'Reclamação', 'desc' => 'Registro de insatisfação com serviços ou atendimentos prestados'],
            ] as $m)
                <div class="rounded-xl border border-indigo-200 bg-indigo-50/60 px-6 py-5">
                    <div class="flex items-center gap-2 text-slate-800 font-semibold">
                        <svg class="w-5 h-5 text-slate-700" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3l7 3v5c0 4.5-3 8-7 10-4-2-7-5.5-7-10V6l7-3z"/></svg>
                        {{ $m['title'] }}
                    </div>
                    <p class="mt-2 text-sm text-slate-600 leading-relaxed">{{ $m['desc'] }}</p>
                </div>
            @endforeach
        </div>
    </section>

    </div>
@endsection