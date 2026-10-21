@php
    $errosEncaminhamento = $errors->encaminhamento;
    $podeEncaminhar = ! $manifestacao->isEncerrada() && $setores->isNotEmpty();
    $campoClasse = 'w-full rounded-md border border-slate-200 bg-slate-50 px-3 py-2 text-sm focus:bg-white focus:border-indigo-400 focus:ring-1 focus:ring-indigo-400 outline-none';
@endphp

<div class="bg-white rounded-lg border border-slate-200 shadow-sm p-6"
     x-data="{
        aberto: {{ $errosEncaminhamento->any() ? 'true' : 'false' }},
        compartilhar: {{ old('compartilhar_dados') ? 'true' : 'false' }},
        confirmar: false,
        enviando: false,
        alternarCompartilhamento() {
            if (this.compartilhar) { this.compartilhar = false; return; }
            this.confirmar = true;
        },
        aceitarCompartilhamento() { this.compartilhar = true; this.confirmar = false; },
        recusarCompartilhamento() { this.compartilhar = false; this.confirmar = false; },
     }">
    <h2 class="font-semibold text-slate-800">Encaminhar para setor</h2>
    <p class="mt-1 text-xs text-slate-500">Direcione a manifestação ao setor responsável. O setor será notificado por e-mail. Informação interna, não visível ao solicitante.</p>

    <button type="button" @click="aberto = true" @disabled(! $podeEncaminhar)
            class="mt-3 w-full flex items-center justify-center gap-2 bg-indigo-600 hover:bg-indigo-700 disabled:bg-slate-300 disabled:cursor-not-allowed text-white text-sm font-medium px-4 py-2.5 rounded-md transition">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 5l7 7-7 7M5 12h14"/></svg>
        Encaminhar manifestação
    </button>
    @if ($manifestacao->isEncerrada())
        <p class="mt-2 text-xs text-slate-400">Manifestações encerradas não podem ser encaminhadas.</p>
    @elseif ($setores->isEmpty())
        <p class="mt-2 text-xs text-slate-400">Nenhum setor ativo cadastrado para receber encaminhamentos.</p>
    @endif

    {{-- Modal de encaminhamento --}}
    <div x-show="aberto" x-cloak class="fixed inset-0 z-40 flex items-center justify-center bg-black/40 px-4">
        <div @click.outside="if (! confirmar && ! enviando) aberto = false" class="bg-white rounded-lg shadow-xl max-w-lg w-full p-6">
            <div class="flex items-start justify-between">
                <div>
                    <h3 class="font-semibold text-slate-800">Encaminhar manifestação</h3>
                    <p class="text-xs text-slate-500">Protocolo {{ $manifestacao->protocolo }}</p>
                </div>
                <button type="button" @click="aberto = false" class="text-slate-400 hover:text-slate-600" aria-label="Fechar">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M6 6l12 12M18 6L6 18"/></svg>
                </button>
            </div>

            <form action="{{ route('admin.encaminhamentos.store', $manifestacao) }}" method="POST" class="mt-4 space-y-4" @submit="enviando = true">
                @csrf

                <div>
                    <label for="setor_destino_id" class="block text-sm font-medium text-slate-600 mb-1">Setor de destino</label>
                    <select id="setor_destino_id" name="setor_destino_id" required class="{{ $campoClasse }}">
                        <option value="">Selecione…</option>
                        @foreach ($setores as $setor)
                            <option value="{{ $setor->id }}" @selected((int) old('setor_destino_id') === $setor->id)>{{ $setor->nome }}</option>
                        @endforeach
                    </select>
                    @error('setor_destino_id', 'encaminhamento') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="motivo" class="block text-sm font-medium text-slate-600 mb-1">Motivo do encaminhamento</label>
                    <textarea id="motivo" name="motivo" rows="4" required minlength="10" maxlength="2000"
                              placeholder="Explique ao setor o que precisa ser analisado ou respondido…"
                              class="{{ $campoClasse }} resize-y">{{ old('motivo') }}</textarea>
                    <p class="mt-1 text-xs text-slate-400">Enviado ao setor por e-mail e registrado no histórico interno.</p>
                    @error('motivo', 'encaminhamento') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <div class="rounded-md border px-4 py-3" :class="compartilhar ? 'border-amber-300 bg-amber-50' : 'border-slate-200 bg-slate-50'">
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <p class="text-sm font-medium text-slate-700">Enviar dados do aluno ao setor</p>
                            <p class="text-xs text-slate-500" x-text="compartilhar ? 'Os dados do aluno e os anexos serão compartilhados.' : 'Somente as informações básicas da manifestação serão enviadas.'"></p>
                        </div>
                        <button type="button" role="switch" :aria-checked="compartilhar.toString()" @click="alternarCompartilhamento()"
                                :class="compartilhar ? 'bg-amber-500' : 'bg-slate-300'"
                                class="relative inline-flex h-6 w-11 shrink-0 items-center rounded-full transition">
                            <span :class="compartilhar ? 'translate-x-6' : 'translate-x-1'" class="inline-block h-4 w-4 rounded-full bg-white shadow transition"></span>
                        </button>
                    </div>
                    <input type="hidden" name="compartilhar_dados" :value="compartilhar ? 1 : 0">
                    <input type="hidden" name="confirmacao_compartilhamento" :value="compartilhar ? 1 : 0">
                    @error('confirmacao_compartilhamento', 'encaminhamento') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" @click="aberto = false" class="text-sm px-4 py-2 rounded-md border border-slate-300 text-slate-700 hover:bg-slate-50">Cancelar</button>
                    <button type="submit" :disabled="enviando" class="text-sm px-4 py-2 rounded-md bg-indigo-600 hover:bg-indigo-700 disabled:opacity-60 text-white flex items-center gap-2">
                        <svg x-show="enviando" x-cloak class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" class="opacity-25"/><path fill="currentColor" class="opacity-75" d="M4 12a8 8 0 018-8v3a5 5 0 00-5 5H4z"/></svg>
                        <span x-text="enviando ? 'Encaminhando…' : 'Encaminhar'"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Pop-up de confirmação do compartilhamento de dados --}}
    <div x-show="confirmar" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 px-4">
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full p-6" role="alertdialog" aria-modal="true">
            <div class="flex items-center gap-3">
                <span class="flex h-9 w-9 items-center justify-center rounded-full bg-amber-100 text-amber-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v4m0 4h.01M10.3 3.9L1.8 18a2 2 0 001.7 3h17a2 2 0 001.7-3L13.7 3.9a2 2 0 00-3.4 0z"/></svg>
                </span>
                <h3 class="font-semibold text-slate-800">Compartilhar dados do aluno?</h3>
            </div>

            <div class="mt-4 space-y-3 text-sm text-slate-600">
                <p>Ao confirmar, os seguintes dados do solicitante serão <strong>enviados por e-mail ao setor selecionado</strong> e ficarão registrados neste encaminhamento:</p>
                <p class="rounded-md bg-slate-50 border border-slate-200 px-3 py-2 text-slate-700">
                    {{ implode(', ', \App\Models\Manifestacao::DADOS_COMPARTILHAVEIS) }} e os anexos da manifestação.
                </p>
                <p><strong>CPF, telefone e e-mail do aluno não são enviados.</strong></p>
                <p>Use esta opção somente quando o setor precisar identificar o aluno para atender a demanda (por exemplo, consultar registros acadêmicos ou financeiros). O compartilhamento ficará registrado com data e hora.</p>
                @if ($manifestacao->tipo === 'Denúncia')
                    <p class="rounded-md border border-red-200 bg-red-50 px-3 py-2 text-red-700">
                        <strong>Atenção:</strong> esta manifestação é uma <strong>denúncia</strong>. Compartilhar a identidade do denunciante pode expô-lo. Confirme apenas se for indispensável.
                    </p>
                @endif
            </div>

            <div class="mt-6 flex justify-end gap-3">
                <button type="button" @click="recusarCompartilhamento()" class="text-sm px-4 py-2 rounded-md border border-slate-300 text-slate-700 hover:bg-slate-50">Cancelar</button>
                <button type="button" @click="aceitarCompartilhamento()" class="text-sm px-4 py-2 rounded-md bg-amber-500 hover:bg-amber-600 text-white">Sim, compartilhar</button>
            </div>
        </div>
    </div>
</div>
