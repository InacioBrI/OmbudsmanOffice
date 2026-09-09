@extends('layouts.admin')

@php
    $badges = [
        'recebida' => 'bg-slate-100 text-slate-700',
        'em_analise' => 'bg-amber-100 text-amber-700',
        'concluida' => 'bg-emerald-100 text-emerald-700',
        'arquivada' => 'bg-slate-200 text-slate-600',
    ];
    $rotaFiltro = match ($secao ?? 'painel') {
        'atendimentos' => 'admin.manifestacoes.atendimentos',
        'historico' => 'admin.manifestacoes.historico',
        default => 'admin.manifestacoes.index',
    };
@endphp

@section('conteudo')
    {{-- Filtros --}}
    <form action="{{ route($rotaFiltro) }}" method="GET"
          class="bg-white rounded-lg border border-slate-200 shadow-sm p-4 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4 items-end">
            <div class="md:col-span-2">
                <label class="block text-xs font-medium text-slate-500 mb-1">Pesquisar</label>
                <div class="relative">
                    <svg class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><circle cx="11" cy="11" r="7"/><path stroke-linecap="round" d="M21 21l-4-4"/></svg>
                    <input type="text" name="busca" value="{{ request('busca') }}" placeholder="Nome, protocolo ou descrição"
                           class="w-full rounded-md border border-slate-200 bg-slate-50 pl-9 pr-3 py-2 text-sm focus:bg-white focus:border-indigo-400 focus:ring-1 focus:ring-indigo-400 outline-none">
                </div>
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-500 mb-1">Categoria</label>
                <select name="categoria" class="w-full rounded-md border border-slate-200 bg-slate-50 px-3 py-2 text-sm focus:bg-white focus:border-indigo-400 focus:ring-1 focus:ring-indigo-400 outline-none">
                    <option value="">Todas</option>
                    @foreach (\App\Models\Manifestacao::TIPOS as $tipo)
                        <option value="{{ $tipo }}" @selected(request('categoria') === $tipo)>{{ $tipo }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-500 mb-1">Status</label>
                <select name="status" class="w-full rounded-md border border-slate-200 bg-slate-50 px-3 py-2 text-sm focus:bg-white focus:border-indigo-400 focus:ring-1 focus:ring-indigo-400 outline-none">
                    <option value="">Todos</option>
                    @foreach (\App\Models\Manifestacao::STATUSES as $valor => $label)
                        <option value="{{ $valor }}" @selected(request('status') === $valor)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex gap-2">
                <div class="flex-1">
                    <label class="block text-xs font-medium text-slate-500 mb-1">Data</label>
                    <input type="date" name="data" value="{{ request('data') }}"
                           class="w-full rounded-md border border-slate-200 bg-slate-50 px-3 py-2 text-sm focus:bg-white focus:border-indigo-400 focus:ring-1 focus:ring-indigo-400 outline-none">
                </div>
                <button type="submit" class="self-end bg-amber-500 hover:bg-amber-600 text-white text-sm font-medium px-4 py-2 rounded-md transition flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 4h18l-7 8v6l-4 2v-8L3 4z"/></svg>
                    Filtrar
                </button>
            </div>
        </div>
    </form>

    {{-- Tabela --}}
    <div class="bg-white rounded-lg border border-slate-200 shadow-sm">
        <div class="px-5 py-4 border-b border-slate-100">
            <h2 class="font-semibold text-slate-800">Manifestações</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-xs uppercase tracking-wide text-slate-400 bg-slate-50">
                        <th class="px-5 py-3">ID</th>
                        <th class="px-5 py-3">Nome</th>
                        <th class="px-5 py-3">Categoria</th>
                        <th class="px-5 py-3">Data</th>
                        <th class="px-5 py-3">Status</th>
                        <th class="px-5 py-3 text-right">Ações</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($manifestacoes as $m)
                        <tr class="hover:bg-slate-50" x-data="{ confirmar: false }">
                            <td class="px-5 py-3 font-mono text-xs text-slate-500">{{ $m->protocolo }}</td>
                            <td class="px-5 py-3 text-slate-700">{{ $m->nome ?: 'Anônimo' }}</td>
                            <td class="px-5 py-3 text-slate-700">{{ $m->tipo }}</td>
                            <td class="px-5 py-3 text-slate-500">{{ $m->created_at->format('d/m/Y') }}</td>
                            <td class="px-5 py-3">
                                <span class="text-xs font-medium px-3 py-1 rounded-full {{ $badges[$m->status] ?? 'bg-slate-100 text-slate-700' }}">
                                    {{ $m->status_label }}
                                </span>
                            </td>
                            <td class="px-5 py-3">
                                <div class="flex items-center justify-end gap-3">
                                    <a href="{{ route('admin.manifestacoes.show', $m) }}" class="text-indigo-600 hover:text-indigo-800">Ver</a>
                                    <button @click="confirmar = true" class="text-red-500 hover:text-red-700">Excluir</button>
                                    <div x-show="confirmar" x-cloak class="fixed inset-0 z-40 flex items-center justify-center bg-black/40 px-4">
                                        <div @click.outside="confirmar = false" class="bg-white rounded-lg shadow-xl max-w-sm w-full p-6">
                                            <h3 class="font-semibold text-slate-800">Excluir manifestação</h3>
                                            <p class="mt-2 text-sm text-slate-600">Esta ação é permanente e removerá também os anexos. Deseja continuar?</p>
                                            <div class="mt-5 flex justify-end gap-3">
                                                <button type="button" @click="confirmar = false" class="text-sm px-4 py-2 rounded-md border border-slate-300 text-slate-700 hover:bg-slate-50">Cancelar</button>
                                                <form action="{{ route('admin.manifestacoes.destroy', $m) }}" method="POST">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-sm px-4 py-2 rounded-md bg-red-600 hover:bg-red-700 text-white">Excluir</button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-16 text-center font-semibold text-slate-500">
                                Nenhuma Manifestação declarada
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($manifestacoes->hasPages())
            <div class="px-5 py-4 border-t border-slate-100">
                {{ $manifestacoes->links() }}
            </div>
        @endif
    </div>
@endsection
