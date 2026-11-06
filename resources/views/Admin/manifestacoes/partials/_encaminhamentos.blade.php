@php
    $badgesEncaminhamento = [
        'pendente' => 'bg-amber-100 text-amber-700',
        'em_atendimento' => 'bg-blue-100 text-blue-700',
        'concluido' => 'bg-emerald-100 text-emerald-700',
        'cancelado' => 'bg-slate-200 text-slate-600',
    ];
    $errosMensagem = $errors->mensagem;
@endphp

<section class="bg-white rounded-lg border border-slate-200 shadow-sm p-6">
    <div class="flex items-center justify-between gap-3">
        <h2 class="font-semibold text-slate-800">Encaminhamentos internos</h2>
    </div>

    <div class="mt-4 space-y-5">
        @forelse ($manifestacao->encaminhamentos as $encaminhamento)
            <article id="encaminhamento-{{ $encaminhamento->id }}" class="rounded-lg border border-slate-200">
                {{-- Cabeçalho --}}
                <div class="px-4 py-3 border-b border-slate-100 bg-slate-50 rounded-t-lg flex flex-wrap items-center justify-between gap-2">
                    <p class="text-sm text-slate-700">
                        <span class="text-slate-500">{{ $encaminhamento->setorOrigem->nome }}</span>
                        <span class="text-slate-400">&rarr;</span>
                        <span class="font-semibold">{{ $encaminhamento->setorDestino->nome }}</span>
                    </p>
                    <span class="text-xs font-medium px-3 py-1 rounded-full {{ $badgesEncaminhamento[$encaminhamento->status] ?? 'bg-slate-100 text-slate-700' }}">
                        {{ $encaminhamento->status_label }}
                    </span>
                </div>

                <div class="px-4 py-3 space-y-3">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm">
                        <div><p class="text-xs uppercase tracking-wide text-slate-400">Encaminhado por</p><p class="text-slate-700">{{ $encaminhamento->responsavel }}</p></div>
                        <div><p class="text-xs uppercase tracking-wide text-slate-400">Data e hora</p><p class="text-slate-700">{{ $encaminhamento->created_at->format('d/m/Y H:i') }}</p></div>
                    </div>
                    <div>
                        <p class="text-xs uppercase tracking-wide text-slate-400">Motivo</p>
                        <p class="text-sm text-slate-700 whitespace-pre-line">{{ $encaminhamento->motivo }}</p>
                    </div>

                    <div class="flex flex-wrap gap-2 text-xs">
                        @if ($encaminhamento->compartilhar_dados)
                            <span class="px-2 py-1 rounded bg-amber-100 text-amber-800">Dados do aluno compartilhados em {{ $encaminhamento->dados_compartilhados_em?->format('d/m/Y H:i') }}</span>
                        @else
                            <span class="px-2 py-1 rounded bg-slate-100 text-slate-600">Sem dados pessoais</span>
                        @endif
                        @if ($encaminhamento->email_enviado_em)
                            <span class="px-2 py-1 rounded bg-emerald-50 text-emerald-700">E-mail enviado em {{ $encaminhamento->email_enviado_em->format('d/m/Y H:i') }}</span>
                        @else
                            <span class="px-2 py-1 rounded bg-orange-50 text-orange-700">E-mail pendente</span>
                        @endif
                    </div>

                    {{-- Ações do encaminhamento --}}
                    @unless ($manifestacao->isEncerrada())
                        <div class="flex flex-wrap gap-2">
                            @foreach (\App\Models\ManifestacaoEncaminhamento::TRANSICOES[$encaminhamento->status] ?? [] as $proximo)
                                @continue($proximo === 'em_atendimento' && $encaminhamento->isAtivo())
                                @php
                                    $acao = match ($proximo) {
                                        'concluido' => ['Marcar como concluído', 'border-emerald-300 text-emerald-700 hover:bg-emerald-50', 'Confirmar que o setor concluiu sua parte do atendimento?'],
                                        default => ['Cancelar encaminhamento', 'border-red-300 text-red-600 hover:bg-red-50', 'Cancelar este encaminhamento? A conversa será mantida no histórico.'],
                                    };
                                @endphp
                                <form action="{{ route('admin.encaminhamentos.status', $encaminhamento) }}" method="POST" onsubmit="return confirm(@js($acao[2]))">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="status" value="{{ $proximo }}">
                                    <button type="submit" class="text-xs font-medium px-3 py-1.5 rounded-md border {{ $acao[1] }} transition">{{ $acao[0] }}</button>
                                </form>
                            @endforeach
                        </div>
                    @endunless
                </div>

                {{-- Comunicação interna --}}
                <div class="border-t border-slate-100 px-4 py-3">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 mb-3">Comunicação interna</p>

                    <div class="space-y-3 max-h-96 overflow-y-auto pr-1">
                        @forelse ($encaminhamento->mensagens as $mensagem)
                            <div class="flex {{ $mensagem->isDaOuvidoria() ? 'justify-end' : 'justify-start' }}">
                                <div class="max-w-[85%] rounded-lg px-3 py-2 {{ $mensagem->isDaOuvidoria() ? 'bg-indigo-50 border border-indigo-100' : 'bg-slate-50 border border-slate-200' }}">
                                    <p class="text-xs font-semibold {{ $mensagem->isDaOuvidoria() ? 'text-indigo-700' : 'text-slate-700' }}">{{ $mensagem->remetente_nome }}</p>
                                    <p class="mt-0.5 text-sm text-slate-700 whitespace-pre-line">{{ $mensagem->corpo }}</p>
                                    <p class="mt-1 text-[11px] text-slate-400">{{ $mensagem->created_at->format('d/m/Y H:i') }}</p>
                                </div>
                            </div>
                        @empty
                            <p class="text-sm text-slate-500">Nenhuma mensagem trocada ainda.</p>
                        @endforelse
                    </div>

                    @if ($encaminhamento->status !== 'cancelado' && ! $manifestacao->isEncerrada())
                        @php $formAtual = (int) old('encaminhamento_form') === $encaminhamento->id; @endphp
                        <form action="{{ route('admin.encaminhamentos.mensagens.store', $encaminhamento) }}" method="POST" class="mt-4 space-y-2"
                              x-data="{ enviando: false }" @submit="enviando = true">
                            @csrf
                            <input type="hidden" name="encaminhamento_form" value="{{ $encaminhamento->id }}">
                            <textarea name="corpo" rows="2" required maxlength="5000" placeholder="Escreva uma mensagem para a conversa interna…"
                                      class="w-full rounded-md border border-slate-200 bg-slate-50 px-3 py-2 text-sm focus:bg-white focus:border-indigo-400 focus:ring-1 focus:ring-indigo-400 outline-none resize-y">{{ $formAtual ? old('corpo') : '' }}</textarea>
                            @if ($formAtual)
                                @error('corpo', 'mensagem') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                                @error('remetente_tipo', 'mensagem') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                            @endif
                            <div class="flex flex-wrap items-center justify-between gap-2">
                                <label class="flex items-center gap-2 text-xs text-slate-500">
                                    Registrar como
                                    <select name="remetente_tipo" class="rounded-md border border-slate-200 bg-white px-2 py-1 text-xs">
                                        <option value="ouvidoria" @selected(! $formAtual || old('remetente_tipo') !== 'setor')>Ouvidoria</option>
                                        <option value="setor" @selected($formAtual && old('remetente_tipo') === 'setor')>{{ $encaminhamento->setorDestino->nome }}</option>
                                    </select>
                                </label>
                                <button type="submit" :disabled="enviando" class="text-sm px-4 py-1.5 rounded-md bg-indigo-600 hover:bg-indigo-700 disabled:opacity-60 text-white">
                                    <span x-text="enviando ? 'Enviando…' : 'Enviar mensagem'">Enviar mensagem</span>
                                </button>
                            </div>
                        </form>
                    @endif
                </div>
            </article>
        @empty
            <p class="text-sm text-slate-500">Esta manifestação ainda não foi encaminhada a nenhum setor.</p>
        @endforelse
    </div>
</section>
