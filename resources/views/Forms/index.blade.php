@extends('layouts.app')

@section('titulo', 'Registrar Manifestação - Ouvidoria BA')

@php
    $inputClasse = 'w-full rounded-md border border-slate-200 bg-slate-100 px-4 py-2.5 text-sm placeholder:text-slate-400 focus:bg-white focus:border-indigo-400 focus:ring-1 focus:ring-indigo-400 outline-none';
    $labelClasse = 'block text-sm font-semibold text-slate-700 mb-1.5';
@endphp

@section('conteudo')
    <div class="py-12 px-6">
        <div class="max-w-3xl mx-auto bg-white rounded-2xl shadow-sm border border-slate-100 p-8 md:p-12"
             x-data="formManifestacao()"
             data-telefone="{{ old('telefone', '') }}"
             data-cpf="{{ old('cpf', '') }}"
             data-descricao="{{ old('descricao', '') }}">
            <h1 class="text-center text-2xl md:text-3xl font-bold text-amber-500 mb-10">
                Registrar Manifestação
            </h1>

            @if ($errors->any())
                <div class="mb-6 rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                    Verifique os campos destacados abaixo.
                </div>
            @endif

            <form action="{{ route('manifestacoes.store') }}" method="POST" enctype="multipart/form-data" class="space-y-8">
                @csrf

                {{-- Dados do Manifestante --}}
                <div>
                    <h2 class="font-semibold text-slate-800 mb-4">Dados do Manifestante</h2>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                        <div class="sm:col-span-2">
                            <label for="nome" class="{{ $labelClasse }}">Nome Completo <span class="text-amber-500">*</span></label>
                            <input type="text" id="nome" name="nome" value="{{ old('nome') }}" class="{{ $inputClasse }}">
                            @error('nome') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="cpf" class="{{ $labelClasse }}">CPF <span class="text-amber-500">*</span></label>
                            <input type="text" id="cpf" name="cpf" placeholder="000.000.000-00"
                                   x-model="cpf" @input="mascaraCpf" maxlength="14" class="{{ $inputClasse }}">
                            @error('cpf') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="rm" class="{{ $labelClasse }}">RM <span class="text-amber-500">*</span></label>
                            <input type="text" id="rm" name="rm" placeholder="000000000" value="{{ old('rm') }}" class="{{ $inputClasse }}">
                            @error('rm') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="vinculo" class="{{ $labelClasse }}">Tipo de Vínculo <span class="text-amber-500">*</span></label>
                            <select id="vinculo" name="vinculo" class="{{ $inputClasse }}">
                                <option value="">Selecione...</option>
                                @foreach (\App\Models\Manifestacao::VINCULOS as $vinculo)
                                    <option value="{{ $vinculo }}" @selected(old('vinculo') === $vinculo)>{{ $vinculo }}</option>
                                @endforeach
                            </select>
                            @error('vinculo') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="curso" class="{{ $labelClasse }}">Curso <span class="text-amber-500">*</span></label>
                            <input type="text" id="curso" name="curso" value="{{ old('curso') }}" class="{{ $inputClasse }}">
                            @error('curso') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="unidade" class="{{ $labelClasse }}">Unidade <span class="text-amber-500">*</span></label>
                            <input type="text" id="unidade" name="unidade" value="{{ old('unidade') }}" class="{{ $inputClasse }}">
                            @error('unidade') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="semestre" class="{{ $labelClasse }}">Semestre <span class="text-amber-500">*</span></label>
                            <input type="text" id="semestre" name="semestre" placeholder="Ex.: 3º semestre" value="{{ old('semestre') }}" class="{{ $inputClasse }}">
                            @error('semestre') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="email" class="{{ $labelClasse }}">E-mail <span class="text-amber-500">*</span></label>
                            <input type="email" id="email" name="email" value="{{ old('email') }}" class="{{ $inputClasse }}">
                            @error('email') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="telefone" class="{{ $labelClasse }}">Telefone <span class="text-amber-500">*</span></label>
                            <input type="tel" id="telefone" name="telefone" placeholder="(00)00000-0000"
                                   x-model="telefone" @input="mascaraTelefone" maxlength="15" class="{{ $inputClasse }}">
                            @error('telefone') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>

                {{-- Dados da Manifestação --}}
                <div>
                    <h2 class="font-semibold text-slate-800 mb-4">Dados da Manifestação</h2>

                    <div class="mb-6">
                        <label class="{{ $labelClasse }}">Tipo de Manifestação <span class="text-amber-500">*</span></label>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            @foreach (\App\Models\Manifestacao::TIPOS as $tipo)
                                <label class="flex items-center gap-3 border border-slate-200 rounded-md px-4 py-3 cursor-pointer hover:border-indigo-400 has-[:checked]:border-indigo-500 has-[:checked]:ring-1 has-[:checked]:ring-indigo-500 transition">
                                    <input type="radio" name="tipo" value="{{ $tipo }}" @checked(old('tipo') === $tipo)
                                           class="text-indigo-600 focus:ring-indigo-500">
                                    <span class="text-sm text-slate-700">{{ $tipo }}</span>
                                </label>
                            @endforeach
                        </div>
                        @error('tipo') <p class="mt-2 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                        <div>
                            <label for="area_envolvida" class="{{ $labelClasse }}">Área Envolvida <span class="text-amber-500">*</span></label>
                            <input type="text" id="area_envolvida" name="area_envolvida" value="{{ old('area_envolvida') }}" class="{{ $inputClasse }}">
                            @error('area_envolvida') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="assunto" class="{{ $labelClasse }}">Assunto <span class="text-amber-500">*</span></label>
                            <input type="text" id="assunto" name="assunto" value="{{ old('assunto') }}" class="{{ $inputClasse }}">
                            @error('assunto') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="data_ocorrido" class="{{ $labelClasse }}">Data do Ocorrido <span class="text-amber-500">*</span></label>
                            <input type="date" id="data_ocorrido" name="data_ocorrido" value="{{ old('data_ocorrido') }}" max="{{ now()->format('Y-m-d') }}" class="{{ $inputClasse }}">
                            @error('data_ocorrido') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="local_ocorrido" class="{{ $labelClasse }}">Local do Ocorrido <span class="text-amber-500">*</span></label>
                            <input type="text" id="local_ocorrido" name="local_ocorrido" value="{{ old('local_ocorrido') }}" class="{{ $inputClasse }}">
                            @error('local_ocorrido') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="mt-6">
                        <label for="pessoas_envolvidas" class="{{ $labelClasse }}">Pessoas Envolvidas <span class="text-amber-500">*</span></label>
                        <textarea id="pessoas_envolvidas" name="pessoas_envolvidas" rows="2" placeholder="Nomes ou cargos das pessoas envolvidas..."
                                  class="{{ $inputClasse }} resize-y">{{ old('pessoas_envolvidas') }}</textarea>
                        @error('pessoas_envolvidas') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div class="mt-6">
                        <label for="descricao" class="{{ $labelClasse }}">Descrição Detalhada da Ocorrência <span class="text-amber-500">*</span></label>
                        <textarea id="descricao" name="descricao" rows="4" placeholder="Descreva detalhadamente sua manifestação..."
                                  x-model="descricao" maxlength="2000"
                                  class="{{ $inputClasse }} resize-y">{{ old('descricao') }}</textarea>
                        <div class="mt-2 flex items-center justify-between">
                            <p class="text-xs text-slate-500">Seja o mais específico possível, incluindo datas, locais e nomes quando relevante.</p>
                            <span class="text-xs text-slate-400" x-text="descricao.length + '/2000'"></span>
                        </div>
                        @error('descricao') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>

                {{-- Anexos --}}
                <div>
                    <h2 class="font-semibold text-slate-800 mb-2">Anexar Documentos (Opcional)</h2>
                    <label for="anexos"
                           @dragover.prevent="arrastando = true" @dragleave.prevent="arrastando = false"
                           @drop.prevent="soltarArquivos($event)"
                           :class="arrastando ? 'border-indigo-500 bg-indigo-50' : 'border-slate-300'"
                           class="flex flex-col items-center justify-center gap-2 border-2 border-dashed rounded-lg py-10 cursor-pointer hover:border-indigo-400 transition text-center">
                        <svg class="w-7 h-7 text-slate-400" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v12m0 0l-4-4m4 4l4-4M5 21h14" />
                        </svg>
                        <span class="text-sm text-slate-500">Click para selecionar arquivos ou arraste aqui</span>
                        <span class="text-xs text-slate-400">PDF, JPG, PNG até 10MB</span>
                        <input type="file" id="anexos" name="anexos[]" multiple accept=".pdf,.jpg,.jpeg,.png"
                               x-ref="inputAnexos" @change="selecionarArquivos($event)" class="hidden">
                    </label>
                    <template x-if="arquivos.length">
                        <ul class="mt-3 space-y-2">
                            <template x-for="(arq, idx) in arquivos" :key="idx">
                                <li class="flex items-center justify-between rounded-md border border-slate-200 bg-slate-50 px-3 py-2 text-xs">
                                    <span class="truncate text-slate-700" x-text="arq.name"></span>
                                    <div class="flex items-center gap-3 shrink-0">
                                        <span class="text-slate-400" x-text="formatarTamanho(arq.size)"></span>
                                        <span x-show="arq.size > 10485760" class="text-red-600">&gt; 10MB</span>
                                        <button type="button" @click="removerArquivo(idx)" class="text-red-500 hover:text-red-700">Remover</button>
                                    </div>
                                </li>
                            </template>
                        </ul>
                    </template>
                    @error('anexos.*') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                {{-- Termo --}}
                <label class="flex items-start gap-3 rounded-md border border-amber-200 bg-amber-50 px-4 py-3">
                    <input type="checkbox" name="ciente" value="1" x-model="ciente" class="mt-0.5 text-indigo-600 focus:ring-indigo-500">
                    <span class="text-xs text-slate-600 leading-relaxed">
                        Declaro estar ciente de que as informações fornecidas serão apuradas conforme os
                        procedimentos estabelecidos e que poderei ser contatado para esclarecimentos adicionais
                    </span>
                </label>
                @error('ciente') <p class="-mt-4 text-xs text-red-600">{{ $message }}</p> @enderror

                {{-- Ações --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2">
                    <a href="{{ route('home') }}"
                       class="text-center border border-slate-300 text-slate-700 font-medium text-sm px-6 py-3 rounded-md hover:bg-slate-50 transition">
                        Cancelar
                    </a>
                    <button type="submit" :disabled="!ciente"
                            :class="ciente ? 'bg-emerald-600 hover:bg-emerald-700' : 'bg-slate-300 cursor-not-allowed'"
                            class="text-white font-medium text-sm px-6 py-3 rounded-md transition">
                        Enviar Manifestação
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function formManifestacao() {
            return {
                telefone: '',
                cpf: '',
                descricao: '',
                ciente: false,
                arquivos: [],
                arrastando: false,
                init() {
                    this.telefone = this.$el.dataset.telefone || '';
                    this.cpf = this.$el.dataset.cpf || '';
                    this.descricao = this.$el.dataset.descricao || '';
                },
                mascaraTelefone() {
                    let v = this.telefone.replace(/\D/g, '').slice(0, 11);
                    if (v.length > 6) {
                        this.telefone = `(${v.slice(0, 2)})${v.slice(2, 7)}-${v.slice(7)}`;
                    } else if (v.length > 2) {
                        this.telefone = `(${v.slice(0, 2)})${v.slice(2)}`;
                    } else {
                        this.telefone = v;
                    }
                },
                mascaraCpf() {
                    let v = this.cpf.replace(/\D/g, '').slice(0, 11);
                    v = v.replace(/(\d{3})(\d)/, '$1.$2');
                    v = v.replace(/(\d{3})(\d)/, '$1.$2');
                    v = v.replace(/(\d{3})(\d{1,2})$/, '$1-$2');
                    this.cpf = v;
                },
                selecionarArquivos(e) {
                    this.arquivos = Array.from(e.target.files);
                },
                soltarArquivos(e) {
                    this.arrastando = false;
                    this.$refs.inputAnexos.files = e.dataTransfer.files;
                    this.arquivos = Array.from(e.dataTransfer.files);
                },
                removerArquivo(idx) {
                    const dt = new DataTransfer();
                    this.arquivos.forEach((f, i) => { if (i !== idx) dt.items.add(f); });
                    this.$refs.inputAnexos.files = dt.files;
                    this.arquivos = Array.from(dt.files);
                },
                formatarTamanho(bytes) {
                    if (bytes < 1024) return bytes + ' B';
                    if (bytes < 1048576) return (bytes / 1024).toFixed(1) + ' KB';
                    return (bytes / 1048576).toFixed(1) + ' MB';
                },
            };
        }
    </script>
@endsection
