@extends('layouts.app')

@section('titulo', 'Acompanhar manifestação - Ouvidoria BA')

@php
    $badges = [
        'recebida' => 'bg-slate-100 text-slate-700',
        'em_analise' => 'bg-amber-100 text-amber-700',
        'concluida' => 'bg-emerald-100 text-emerald-700',
        'arquivada' => 'bg-slate-200 text-slate-600',
    ];
@endphp

@section('conteudo')
    <div class="py-16 px-6">
        <div class="max-w-xl mx-auto">
            <h1 class="text-2xl font-bold text-slate-800 text-center">Acompanhar manifestação</h1>
            <p class="mt-2 text-sm text-slate-600 text-center">Informe o número do protocolo recebido no registro.</p>

            <form action="{{ route('manifestacoes.acompanhar') }}" method="GET"
                  class="mt-6 flex gap-3 bg-white rounded-xl border border-slate-100 shadow-sm p-4">
                <input type="text" name="protocolo" value="{{ $protocolo }}" placeholder="OUV-AAAAMMDD-XXXXXX"
                       class="flex-1 rounded-md border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm focus:bg-white focus:border-indigo-400 focus:ring-1 focus:ring-indigo-400 outline-none">
                <button type="submit"
                        class="bg-amber-500 hover:bg-amber-600 text-white font-medium text-sm px-6 py-2.5 rounded-md transition">
                    Consultar
                </button>
            </form>

            @if ($naoEncontrado)
                <div class="mt-6 rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                    Nenhuma manifestação encontrada para o protocolo informado.
                </div>
            @endif

            @if ($manifestacao)
                <div class="mt-6 bg-white rounded-xl border border-slate-100 shadow-sm p-6 space-y-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs uppercase tracking-wide text-slate-400">Protocolo</p>
                            <p class="font-semibold text-slate-800">{{ $manifestacao->protocolo }}</p>
                        </div>
                        <span class="text-xs font-medium px-3 py-1 rounded-full {{ $badges[$manifestacao->status] ?? 'bg-slate-100 text-slate-700' }}">
                            {{ $manifestacao->status_label }}
                        </span>
                    </div>

                    <div class="grid grid-cols-2 gap-4 text-sm">
                        <div>
                            <p class="text-xs uppercase tracking-wide text-slate-400">Tipo</p>
                            <p class="text-slate-700">{{ $manifestacao->tipo }}</p>
                        </div>
                        <div>
                            <p class="text-xs uppercase tracking-wide text-slate-400">Registrada em</p>
                            <p class="text-slate-700">{{ $manifestacao->created_at->format('d/m/Y H:i') }}</p>
                        </div>
                    </div>

                    <div>
                        <p class="text-xs uppercase tracking-wide text-slate-400">Descrição</p>
                        <p class="text-sm text-slate-700 whitespace-pre-line">{{ $manifestacao->descricao }}</p>
                    </div>

                    @if ($manifestacao->resposta)
                        <div class="rounded-md border border-emerald-200 bg-emerald-50 px-4 py-3">
                            <p class="text-xs uppercase tracking-wide text-emerald-600">Resposta da Ouvidoria</p>
                            <p class="mt-1 text-sm text-slate-700 whitespace-pre-line">{{ $manifestacao->resposta }}</p>
                            @if ($manifestacao->respondido_em)
                                <p class="mt-2 text-xs text-slate-400">Respondido em {{ $manifestacao->respondido_em->format('d/m/Y H:i') }}</p>
                            @endif
                        </div>
                    @endif
                </div>
            @endif
        </div>
    </div>
@endsection
