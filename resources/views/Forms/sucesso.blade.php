@extends('layouts.app')

@section('titulo', 'Manifestação registrada - Ouvidoria BA')

@section('conteudo')
    <div class="py-16 px-6">
        <div class="max-w-xl mx-auto bg-white rounded-2xl shadow-sm border border-slate-100 p-8 md:p-12 text-center">
            <div class="mx-auto w-14 h-14 rounded-full bg-emerald-100 flex items-center justify-center">
                <svg class="w-8 h-8 text-emerald-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                </svg>
            </div>
            <h1 class="mt-5 text-2xl font-bold text-slate-800">Manifestação registrada!</h1>
            <p class="mt-2 text-sm text-slate-600">
                Guarde seu protocolo para acompanhar o andamento da sua manifestação.
            </p>

            <div class="mt-6 rounded-lg border border-indigo-200 bg-indigo-50 px-6 py-4">
                <p class="text-xs uppercase tracking-wide text-indigo-500">Protocolo</p>
                <p class="mt-1 text-xl font-bold text-indigo-900 tracking-wider">{{ $manifestacao->protocolo }}</p>
            </div>

            <div class="mt-8 flex flex-col sm:flex-row gap-3 justify-center">
                <a href="{{ route('manifestacoes.acompanhar', ['protocolo' => $manifestacao->protocolo]) }}"
                   class="bg-amber-500 hover:bg-amber-600 text-white font-medium text-sm px-6 py-3 rounded-md transition">
                    Acompanhar manifestação
                </a>
                <a href="{{ route('home') }}"
                   class="border border-slate-300 text-slate-700 font-medium text-sm px-6 py-3 rounded-md hover:bg-slate-50 transition">
                    Voltar ao início
                </a>
            </div>
        </div>
    </div>
@endsection
