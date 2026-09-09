@extends('layouts.admin')

@php
    $badges = [
        'recebida' => 'bg-slate-100 text-slate-700',
        'em_analise' => 'bg-amber-100 text-amber-700',
        'concluida' => 'bg-emerald-100 text-emerald-700',
        'arquivada' => 'bg-slate-200 text-slate-600',
    ];
@endphp

@section('conteudo')
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 mb-8">
        <div class="bg-white rounded-lg border border-slate-200 shadow-sm p-6">
            <p class="text-sm text-slate-500">Total de manifestações</p>
            <p class="mt-2 text-3xl font-bold text-slate-800">{{ $total }}</p>
        </div>
        <div class="bg-white rounded-lg border border-slate-200 shadow-sm p-6">
            <p class="text-sm text-slate-500">Em andamento</p>
            <p class="mt-2 text-3xl font-bold text-amber-600">{{ $emAndamento }}</p>
        </div>
        <div class="bg-white rounded-lg border border-slate-200 shadow-sm p-6">
            <p class="text-sm text-slate-500">Encerradas</p>
            <p class="mt-2 text-3xl font-bold text-emerald-600">{{ $encerradas }}</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white rounded-lg border border-slate-200 shadow-sm p-6">
            <h2 class="font-semibold text-slate-800 mb-4">Por status</h2>
            <div class="space-y-3">
                @foreach (\App\Models\Manifestacao::STATUSES as $valor => $label)
                    @php $qtd = $porStatus[$valor] ?? 0; $pct = $total ? round($qtd / $total * 100) : 0; @endphp
                    <div>
                        <div class="flex items-center justify-between text-sm mb-1">
                            <span class="flex items-center gap-2">
                                <span class="text-xs font-medium px-2 py-0.5 rounded-full {{ $badges[$valor] }}">{{ $label }}</span>
                            </span>
                            <span class="text-slate-500">{{ $qtd }}</span>
                        </div>
                        <div class="w-full h-2 rounded-full bg-slate-100">
                            <div class="h-2 rounded-full bg-indigo-400" style="width: {{ $pct }}%"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="bg-white rounded-lg border border-slate-200 shadow-sm p-6">
            <h2 class="font-semibold text-slate-800 mb-4">Por categoria</h2>
            <div class="space-y-3">
                @foreach (\App\Models\Manifestacao::TIPOS as $tipo)
                    @php $qtd = $porCategoria[$tipo] ?? 0; $pct = $total ? round($qtd / $total * 100) : 0; @endphp
                    <div>
                        <div class="flex items-center justify-between text-sm mb-1">
                            <span class="text-slate-700">{{ $tipo }}</span>
                            <span class="text-slate-500">{{ $qtd }}</span>
                        </div>
                        <div class="w-full h-2 rounded-full bg-slate-100">
                            <div class="h-2 rounded-full bg-amber-400" style="width: {{ $pct }}%"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@endsection
