<!DOCTYPE html>
<html lang="pt-BR" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $titulo ?? 'Painel' }} - Ouvidoria BA</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-100 text-slate-800 antialiased font-sans" x-data="{ sidebar: false }">
<div class="min-h-screen flex">

    {{-- ===================== SIDEBAR ===================== --}}
    <aside :class="sidebar ? 'translate-x-0' : '-translate-x-full'"
           class="fixed inset-y-0 left-0 z-30 w-64 bg-[#0b1f3a] text-white flex flex-col transition-transform md:translate-x-0 md:static">
        <div class="px-5 py-5 flex items-center gap-3 border-b border-white/10">
            <div class="w-9 h-9 rounded-md bg-white flex items-center justify-center">
                <svg class="w-5 h-5 text-[#0b1f3a]" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 3l7 3v5c0 4.5-3 8-7 10-4-2-7-5.5-7-10V6l7-3z" />
                </svg>
            </div>
            <span class="font-semibold text-lg">Ouvidoria</span>
        </div>

        <nav class="flex-1 px-3 py-4">
            <p class="px-3 text-[11px] uppercase tracking-wide text-slate-400 mb-2">Seções</p>
            @php
                $itens = [
                    ['secao' => 'painel', 'label' => 'Painel Principal', 'rota' => 'admin.manifestacoes.index'],
                    ['secao' => 'atendimentos', 'label' => 'Atendimentos', 'rota' => 'admin.manifestacoes.atendimentos'],
                    ['secao' => 'reclame_aqui', 'label' => 'Reclame Aqui', 'rota' => 'admin.reclameAqui.index'],
                    ['secao' => 'historico', 'label' => 'Histórico de manifestação', 'rota' => 'admin.manifestacoes.historico'],
                ];
            @endphp
            @foreach ($itens as $item)
                <a href="{{ route($item['rota']) }}"
                   class="flex items-center gap-3 px-3 py-2.5 rounded-md text-sm mb-1 transition
                          {{ ($secao ?? '') === $item['secao'] ? 'bg-white/10 text-white' : 'text-slate-300 hover:bg-white/5' }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><rect x="4" y="4" width="16" height="16" rx="2"/></svg>
                    {{ $item['label'] }}
                </a>
            @endforeach
        </nav>

        <div class="px-3 py-4 border-t border-white/10 space-y-3">
            <a href="{{ route('admin.dashboard') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-md text-sm transition
                      {{ ($secao ?? '') === 'dashboard' ? 'bg-white/10 text-white' : 'text-slate-300 hover:bg-white/5' }}">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/></svg>
                Dashboard
            </a>
            <div class="flex items-center gap-3 px-3">
                <div class="w-8 h-8 rounded-full bg-slate-600 flex items-center justify-center text-xs font-semibold">IB</div>
                <span class="text-sm text-slate-200">Inácio Barboza</span>
            </div>
        </div>
    </aside>

    <div @click="sidebar = false" x-show="sidebar" class="fixed inset-0 z-20 bg-black/40 md:hidden" x-cloak></div>

    {{-- ===================== MAIN ===================== --}}
    <div class="flex-1 flex flex-col min-w-0">
        <header class="bg-amber-500 text-white px-6 py-4 flex items-center gap-3">
            <button @click="sidebar = !sidebar" class="md:hidden">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
            </button>
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24"><rect x="4" y="4" width="16" height="16" rx="2"/></svg>
            <h1 class="font-semibold text-lg">{{ $titulo ?? 'Painel' }}</h1>
        </header>

        <main class="flex-1 p-6">
            @if (session('sucesso'))
                <div class="mb-4 rounded-md border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                    {{ session('sucesso') }}
                </div>
            @endif

            @yield('conteudo')
        </main>
    </div>
</div>
</body>
</html>
