@extends('layouts.app')

@section('titulo', 'Acompanhar manifestação - Ouvidoria BA')

@php
    $destaques = [
        'recebida' => 'border-slate-200 bg-slate-50 text-slate-700',
        'resposta' => 'border-indigo-200 bg-indigo-50 text-indigo-800',
        'finalizada' => 'border-emerald-200 bg-emerald-50 text-emerald-800',
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
                <div class="mt-6 bg-white rounded-xl border border-slate-100 shadow-sm p-6 space-y-6">
                    <div class="flex flex-wrap items-start justify-between gap-4">
                        <div>
                            <p class="text-xs uppercase tracking-wide text-slate-400">Protocolo</p>
                            <p class="font-semibold text-slate-800">{{ $manifestacao->protocolo }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-xs uppercase tracking-wide text-slate-400">Registrada em</p>
                            <p class="text-sm text-slate-700">{{ $manifestacao->created_at->format('d/m/Y H:i') }}</p>
                        </div>
                    </div>

                    <div class="rounded-lg border px-4 py-3 {{ $destaques[$acompanhamento['atual']] ?? 'border-amber-200 bg-amber-50 text-amber-800' }}">
                        <p class="text-xs uppercase tracking-wide opacity-70">Situação atual</p>
                        <p class="mt-1 font-semibold">{{ $acompanhamento['mensagem'] }}</p>
                    </div>

                    <div>
                        <p class="text-xs uppercase tracking-wide text-slate-400 mb-4">Etapas do atendimento</p>
                        <ol>
                            @foreach ($acompanhamento['etapas'] as $etapa)
                                <li class="relative flex gap-4 {{ $loop->last ? '' : 'pb-6' }}">
                                    @unless ($loop->last)
                                        <span aria-hidden="true"
                                              class="absolute left-4 top-9 bottom-0 w-0.5 -translate-x-1/2 {{ $etapa['estado'] === 'concluida' ? 'bg-emerald-400' : 'bg-slate-200' }}"></span>
                                    @endunless

                                    @if ($etapa['estado'] === 'concluida')
                                        <span class="relative z-10 flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-emerald-500 text-white">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                            </svg>
                                        </span>
                                    @elseif ($etapa['estado'] === 'atual')
                                        <span class="relative z-10 flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-amber-500 ring-4 ring-amber-100">
                                            <span class="h-2.5 w-2.5 rounded-full bg-white animate-pulse"></span>
                                        </span>
                                    @else
                                        <span class="relative z-10 flex h-8 w-8 shrink-0 items-center justify-center rounded-full border-2 border-slate-200 bg-white text-xs font-semibold text-slate-400">
                                            {{ $loop->iteration }}
                                        </span>
                                    @endif

                                    <div class="pt-1">
                                        <p class="text-sm font-medium {{ $etapa['estado'] === 'pendente' ? 'text-slate-400' : 'text-slate-800' }}">
                                            {{ $etapa['titulo'] }}
                                        </p>
                                        @if ($etapa['estado'] !== 'pendente')
                                            <p class="mt-0.5 text-xs text-slate-500">{{ $etapa['descricao'] }}</p>
                                        @endif
                                    </div>
                                </li>
                            @endforeach
                        </ol>
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection
