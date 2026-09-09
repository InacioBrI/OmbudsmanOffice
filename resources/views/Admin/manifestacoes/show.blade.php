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
    <div class="mb-4">
        <a href="{{ route('admin.manifestacoes.index') }}" class="text-sm text-indigo-600 hover:text-indigo-800">&larr; Voltar</a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Dados --}}
        <div class="lg:col-span-2 bg-white rounded-lg border border-slate-200 shadow-sm p-6 space-y-5">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-xs uppercase tracking-wide text-slate-400">Protocolo</p>
                    <p class="font-semibold text-slate-800">{{ $manifestacao->protocolo }}</p>
                </div>
                <span class="text-xs font-medium px-3 py-1 rounded-full {{ $badges[$manifestacao->status] ?? 'bg-slate-100 text-slate-700' }}">
                    {{ $manifestacao->status_label }}
                </span>
            </div>

            <div class="grid grid-cols-2 gap-4 text-sm">
                <div><p class="text-xs uppercase tracking-wide text-slate-400">Tipo</p><p class="text-slate-700">{{ $manifestacao->tipo }}</p></div>
                <div><p class="text-xs uppercase tracking-wide text-slate-400">Registrada em</p><p class="text-slate-700">{{ $manifestacao->created_at->format('d/m/Y H:i') }}</p></div>
                <div><p class="text-xs uppercase tracking-wide text-slate-400">Nome</p><p class="text-slate-700">{{ $manifestacao->nome ?: 'Anônimo' }}</p></div>
                <div><p class="text-xs uppercase tracking-wide text-slate-400">RM</p><p class="text-slate-700">{{ $manifestacao->rm ?: '—' }}</p></div>
                <div><p class="text-xs uppercase tracking-wide text-slate-400">Telefone</p><p class="text-slate-700">{{ $manifestacao->telefone ?: '—' }}</p></div>
                <div><p class="text-xs uppercase tracking-wide text-slate-400">Email</p><p class="text-slate-700">{{ $manifestacao->email ?: '—' }}</p></div>
            </div>

            <div>
                <p class="text-xs uppercase tracking-wide text-slate-400">Descrição</p>
                <p class="text-sm text-slate-700 whitespace-pre-line">{{ $manifestacao->descricao }}</p>
            </div>

            @if (!empty($manifestacao->anexos))
                <div>
                    <p class="text-xs uppercase tracking-wide text-slate-400 mb-2">Anexos</p>
                    <ul class="space-y-2">
                        @foreach ($manifestacao->anexos as $anexo)
                            <li>
                                <a href="{{ asset('storage/' . $anexo['path']) }}" target="_blank"
                                   class="flex items-center gap-2 text-sm text-indigo-600 hover:text-indigo-800">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.5 3.5l5 5M4 20l4.5-.9L20 7.6a2 2 0 000-2.8l-.8-.8a2 2 0 00-2.8 0L5 15.5 4 20z"/></svg>
                                    {{ $anexo['nome'] ?? 'arquivo' }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>

        {{-- Ações --}}
        <div class="space-y-6">
            <form action="{{ route('admin.manifestacoes.update', $manifestacao) }}" method="POST"
                  class="bg-white rounded-lg border border-slate-200 shadow-sm p-6 space-y-4">
                @csrf
                @method('PUT')
                <h2 class="font-semibold text-slate-800">Atualizar manifestação</h2>

                <div>
                    <label class="block text-sm font-medium text-slate-600 mb-1">Status</label>
                    <select name="status" class="w-full rounded-md border border-slate-200 bg-slate-50 px-3 py-2 text-sm focus:bg-white focus:border-indigo-400 focus:ring-1 focus:ring-indigo-400 outline-none">
                        @foreach (\App\Models\Manifestacao::STATUSES as $valor => $label)
                            <option value="{{ $valor }}" @selected($manifestacao->status === $valor)>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('status') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-600 mb-1">Resposta / Parecer</label>
                    <textarea name="resposta" rows="5" placeholder="Escreva a resposta ao cidadão..."
                              class="w-full rounded-md border border-slate-200 bg-slate-50 px-3 py-2 text-sm focus:bg-white focus:border-indigo-400 focus:ring-1 focus:ring-indigo-400 outline-none resize-y">{{ old('resposta', $manifestacao->resposta) }}</textarea>
                    <p class="mt-1 text-xs text-slate-400">Visível para o cidadão no acompanhamento por protocolo.</p>
                </div>

                <button type="submit" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium px-4 py-2.5 rounded-md transition">
                    Salvar alterações
                </button>
            </form>

            <div class="bg-white rounded-lg border border-red-200 shadow-sm p-6" x-data="{ confirmar: false }">
                <h2 class="font-semibold text-red-700">Excluir</h2>
                <p class="mt-1 text-xs text-slate-500">Remove a manifestação e seus anexos permanentemente.</p>
                <button @click="confirmar = true" class="mt-3 w-full border border-red-300 text-red-600 hover:bg-red-50 text-sm font-medium px-4 py-2 rounded-md transition">
                    Excluir manifestação
                </button>
                <div x-show="confirmar" x-cloak class="fixed inset-0 z-40 flex items-center justify-center bg-black/40 px-4">
                    <div @click.outside="confirmar = false" class="bg-white rounded-lg shadow-xl max-w-sm w-full p-6">
                        <h3 class="font-semibold text-slate-800">Excluir manifestação</h3>
                        <p class="mt-2 text-sm text-slate-600">Esta ação é permanente. Deseja continuar?</p>
                        <div class="mt-5 flex justify-end gap-3">
                            <button type="button" @click="confirmar = false" class="text-sm px-4 py-2 rounded-md border border-slate-300 text-slate-700 hover:bg-slate-50">Cancelar</button>
                            <form action="{{ route('admin.manifestacoes.destroy', $manifestacao) }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-sm px-4 py-2 rounded-md bg-red-600 hover:bg-red-700 text-white">Excluir</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
