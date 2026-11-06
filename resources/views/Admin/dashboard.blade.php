@extends('layouts.admin')

@php
    $coresStatus = [
        'recebida' => ['classe' => 'bg-slate-500', 'hex' => '#64748b'],
        'em_analise' => ['classe' => 'bg-amber-500', 'hex' => '#f59e0b'],
        'encaminhada' => ['classe' => 'bg-sky-500', 'hex' => '#0ea5e9'],
        'aguardando_resposta' => ['classe' => 'bg-orange-500', 'hex' => '#f97316'],
        'respondida' => ['classe' => 'bg-indigo-500', 'hex' => '#6366f1'],
        'concluida' => ['classe' => 'bg-emerald-500', 'hex' => '#10b981'],
        'arquivada' => ['classe' => 'bg-slate-800', 'hex' => '#1e293b'],
    ];
    $status = \App\Models\Manifestacao::STATUSES;
    $tipos = \App\Models\Manifestacao::TIPOS;
    $dadosGraficos = [
        'mensal' => $serieMensal,
        'status' => [
            'labels' => array_values($status),
            'values' => array_map(fn ($chave) => (int) ($porStatus[$chave] ?? 0), array_keys($status)),
            'colors' => array_column($coresStatus, 'hex'),
        ],
        'categorias' => [
            'labels' => $tipos,
            'values' => array_map(fn ($tipo) => (int) ($porCategoria[$tipo] ?? 0), $tipos),
        ],
    ];
@endphp

@section('conteudo')
    <div id="dashboard" class="mx-auto flex max-w-7xl flex-col gap-6">
        <div class="flex flex-wrap items-end justify-between gap-3">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-amber-700">Visão geral</p>
                <h2 class="mt-1 text-2xl font-semibold tracking-tight text-[#0b1f3a]">Panorama das manifestações</h2>
                <p class="mt-1 text-sm text-slate-500">Acompanhe os indicadores e a evolução dos atendimentos.</p>
            </div>
            <span class="rounded-full border border-slate-200 bg-white px-3 py-1.5 text-xs font-medium text-slate-600">Dados atualizados nesta visita</span>
        </div>

        <div class="grid gap-4 sm:grid-cols-3">
            <div class="relative overflow-hidden rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <span class="absolute right-5 top-5 flex h-10 w-10 items-center justify-center rounded-xl bg-slate-100 text-[#0b1f3a]" aria-hidden="true">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24"><rect x="4" y="4" width="16" height="16" rx="3"/><path d="M8 9h8M8 13h8M8 17h5"/></svg>
                </span>
                <p class="pr-12 text-sm text-slate-500">Total de manifestações</p>
                <p class="mt-5 text-4xl font-semibold tracking-tight text-[#0b1f3a]">{{ number_format($total, 0, ',', '.') }}</p>
                <p class="mt-3 border-t border-slate-100 pt-3 text-xs text-slate-500">Todos os registros</p>
            </div>
            <div class="relative overflow-hidden rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <span class="absolute right-5 top-5 flex h-10 w-10 items-center justify-center rounded-xl bg-amber-50 text-amber-700" aria-hidden="true">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24"><circle cx="12" cy="12" r="8"/><path d="M12 7v5l3 2"/></svg>
                </span>
                <p class="pr-12 text-sm text-slate-500">Em andamento</p>
                <p class="mt-5 text-4xl font-semibold tracking-tight text-amber-600">{{ number_format($emAndamento, 0, ',', '.') }}</p>
                <p class="mt-3 border-t border-slate-100 pt-3 text-xs text-slate-500">Em atendimento ou aguardando resposta</p>
            </div>
            <div class="relative overflow-hidden rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <span class="absolute right-5 top-5 flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-50 text-emerald-700" aria-hidden="true">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24"><circle cx="12" cy="12" r="8"/><path d="m8 12 3 3 5-6"/></svg>
                </span>
                <p class="pr-12 text-sm text-slate-500">Encerradas</p>
                <p class="mt-5 text-4xl font-semibold tracking-tight text-emerald-600">{{ number_format($encerradas, 0, ',', '.') }}</p>
                <p class="mt-3 border-t border-slate-100 pt-3 text-xs text-slate-500">Concluídas ou arquivadas</p>
            </div>
        </div>

        <div class="grid gap-4 xl:grid-cols-[minmax(0,1.8fr)_minmax(290px,1fr)]">
            <section class="min-w-0 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6" aria-labelledby="tendencia-titulo">
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div>
                        <h3 id="tendencia-titulo" class="font-semibold text-[#0b1f3a]">Evolução mensal</h3>
                        <p class="mt-1 text-xs text-slate-500">Manifestações registradas nos últimos 7 meses</p>
                    </div>
                    <span class="flex items-center gap-2 text-xs text-slate-500"><span class="h-2 w-2 rounded-full bg-[#0b1f3a]"></span> Últimos 7 meses</span>
                </div>
                <div class="mt-5 flex flex-wrap gap-1 border-b border-slate-100" role="group" aria-label="Série da evolução mensal">
                    <button type="button" data-series="total" aria-pressed="true" class="border-b-2 border-[#0b1f3a] px-3 py-2 text-xs font-semibold text-[#0b1f3a] focus-visible:rounded-sm focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-amber-600">Total geral</button>
                    <button type="button" data-series="emAndamento" aria-pressed="false" class="border-b-2 border-transparent px-3 py-2 text-xs font-medium text-slate-500 focus-visible:rounded-sm focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-amber-600">Em andamento</button>
                    <button type="button" data-series="encerradas" aria-pressed="false" class="border-b-2 border-transparent px-3 py-2 text-xs font-medium text-slate-500 focus-visible:rounded-sm focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-amber-600">Encerradas</button>
                </div>
                <div id="grafico-tendencia" class="mt-5 min-h-72" aria-label="Gráfico de evolução mensal"></div>
            </section>

            <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6" aria-labelledby="resumo-titulo">
                <h3 id="resumo-titulo" class="font-semibold text-[#0b1f3a]">Resumo por status</h3>
                <p class="mt-1 text-xs text-slate-500">Distribuição de todas as manifestações</p>
                <div class="mt-5 space-y-3">
                    @foreach ($status as $valor => $label)
                        @php $qtd = (int) ($porStatus[$valor] ?? 0); $pct = $total ? round($qtd / $total * 100) : 0; @endphp
                        <div>
                            <div class="mb-1.5 flex items-center justify-between gap-3 text-xs">
                                <span class="text-slate-600">{{ $label }}</span>
                                <span class="font-semibold tabular-nums text-[#0b1f3a]">{{ number_format($qtd, 0, ',', '.') }}</span>
                            </div>
                            <div class="h-1.5 overflow-hidden rounded-full bg-slate-100">
                                <div class="h-full rounded-full {{ $coresStatus[$valor]['classe'] }}" @style(['width: '.$pct.'%'])></div>
                            </div>
                        </div>
                    @endforeach
                </div>
                <div class="mt-5 flex items-center justify-between border-t border-slate-100 pt-4 text-sm">
                    <span class="text-slate-500">Total geral</span>
                    <strong class="tabular-nums text-[#0b1f3a]">{{ number_format($total, 0, ',', '.') }}</strong>
                </div>
            </section>
        </div>

        <div class="grid gap-4 xl:grid-cols-2">
            <section class="min-w-0 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6" aria-labelledby="categorias-titulo">
                <h3 id="categorias-titulo" class="font-semibold text-[#0b1f3a]">Manifestações por categoria</h3>
                <p class="mt-1 text-xs text-slate-500">Volume por tipo de manifestação</p>
                @if ($total)
                    <div id="grafico-categorias" class="mt-5 min-h-64" aria-label="Gráfico por categoria"></div>
                @else
                    <p class="mt-5 rounded-xl bg-slate-50 p-6 text-sm text-slate-500">Ainda não há dados para o gráfico.</p>
                @endif
                <div class="mt-4 grid gap-x-6 gap-y-2 border-t border-slate-100 pt-4 sm:grid-cols-2">
                    @foreach ($tipos as $tipo)
                        <div class="flex justify-between gap-3 text-xs">
                            <span class="text-slate-600">{{ $tipo }}</span>
                            <strong class="tabular-nums text-[#0b1f3a]">{{ number_format($porCategoria[$tipo] ?? 0, 0, ',', '.') }}</strong>
                        </div>
                    @endforeach
                </div>
            </section>

            <section class="min-w-0 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6" aria-labelledby="distribuicao-titulo">
                <h3 id="distribuicao-titulo" class="font-semibold text-[#0b1f3a]">Distribuição por status</h3>
                <p class="mt-1 text-xs text-slate-500">Participação de cada etapa no total</p>
                <div class="mt-5 grid items-center gap-5 sm:grid-cols-[minmax(0,1fr)_minmax(0,1fr)]">
                    @if ($total)
                        <div id="grafico-status" class="min-w-0 min-h-64" aria-label="Gráfico de distribuição por status"></div>
                    @else
                        <p class="rounded-xl bg-slate-50 p-6 text-sm text-slate-500">Ainda não há dados para o gráfico.</p>
                    @endif
                    <div class="space-y-3">
                        @foreach ($status as $valor => $label)
                            @php $qtd = (int) ($porStatus[$valor] ?? 0); @endphp
                            <div class="flex items-start justify-between gap-2 text-xs">
                                <span class="flex min-w-0 items-start gap-2 text-slate-600">
                                    <span class="mt-1 h-2 w-2 shrink-0 rounded-full {{ $coresStatus[$valor]['classe'] }}"></span>{{ $label }}
                                </span>
                                <span class="shrink-0 tabular-nums text-[#0b1f3a]">{{ number_format($qtd, 0, ',', '.') }} <span class="text-slate-400">({{ $total ? round($qtd / $total * 100) : 0 }}%)</span></span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </section>
        </div>
    </div>
    <script type="application/json" id="dashboard-chart-data">@json($dadosGraficos)</script>
@endsection
