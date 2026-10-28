@extends('layouts.app')

@section('titulo', 'Registrar Manifestação - Ouvidoria BA')

@use('App\Models\Manifestacao')

@php
    $inputClasse = 'w-full rounded-md border border-slate-200 bg-slate-100 px-4 py-2.5 text-sm placeholder:text-slate-400 focus:bg-white focus:border-indigo-400 focus:ring-1 focus:ring-indigo-400 outline-none';
    $labelClasse = 'block text-sm font-semibold text-slate-700 mb-1.5';
    $secaoClasse = 'rounded-xl border border-slate-200 p-5 sm:p-6';
    $legendaClasse = 'px-2 -ml-2 text-base font-semibold text-slate-800';
    $obrigatorio = '<span class="text-amber-500" aria-hidden="true">*</span>';

    $camposIdentificacao = [
        'nome' => ['tipo' => 'text', 'col' => 'sm:col-span-2', 'autocomplete' => 'name'],
        'aluno_nome' => ['tipo' => 'text', 'col' => 'sm:col-span-2'],
        'organizacao' => ['tipo' => 'text', 'col' => 'sm:col-span-2', 'autocomplete' => 'organization'],
        'cpf' => ['tipo' => 'text', 'inputmode' => 'numeric', 'mascara' => 'mascaraDocumento'],
        'rm' => ['tipo' => 'text', 'inputmode' => 'numeric', 'placeholder' => 'Ex.: 000000000', 'maxlength' => 50],
        'ra' => ['tipo' => 'text', 'inputmode' => 'numeric', 'placeholder' => 'Ex.: 000000000', 'maxlength' => 50],
        'curso' => ['tipo' => 'text'],
        'semestre' => ['tipo' => 'select', 'opcoes' => Manifestacao::semestres()],
        'unidade' => ['tipo' => 'select', 'opcoes' => Manifestacao::UNIDADES],
        'telefone' => ['tipo' => 'tel', 'inputmode' => 'tel', 'autocomplete' => 'tel', 'mascara' => 'mascaraTelefone', 'placeholder' => '(00)00000-0000', 'maxlength' => 15],
        'email' => ['tipo' => 'email', 'autocomplete' => 'email', 'placeholder' => 'nome@exemplo.com'],
    ];

    $estadoInicial = collect([...array_keys($camposIdentificacao), 'vinculo', 'possui_pessoas_envolvidas', 'pessoas_envolvidas', 'descricao'])
        ->mapWithKeys(fn ($campo) => [$campo => (string) old($campo, '')])
        ->all();

    $config = [
        'valores' => $estadoInicial,
        'campos' => Manifestacao::CAMPOS_POR_VINCULO,
        'rotulos' => Manifestacao::ROTULOS_CAMPOS,
        'rotulosPorVinculo' => Manifestacao::ROTULOS_POR_VINCULO,
    ];
@endphp

@section('conteudo')
    <div class="py-8 sm:py-12 px-4 sm:px-6">
        <div class="max-w-3xl mx-auto bg-white rounded-2xl shadow-sm border border-slate-100 p-5 sm:p-8 md:p-12"
             x-data="formManifestacao(@js($config))">
            <h1 class="text-center text-2xl md:text-3xl font-bold text-amber-500 mb-3">
                Registrar Manifestação
            </h1>
            <p class="text-center text-xs text-slate-500 mb-8">Campos marcados com {!! $obrigatorio !!} são obrigatórios.</p>

            @if ($errors->any())
                <div class="mb-6 rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700" role="alert">
                    Verifique os campos destacados abaixo.
                </div>
            @endif

            <form action="{{ route('manifestacoes.store') }}" method="POST" enctype="multipart/form-data" class="space-y-8">
                @csrf

                {{-- 1. Identificação do manifestante --}}
                <fieldset class="{{ $secaoClasse }}">
                    <legend class="{{ $legendaClasse }}">1. Identificação do manifestante</legend>

                    <div class="mt-2">
                        <label for="vinculo" class="{{ $labelClasse }}">Qual é o seu vínculo com a instituição? {!! $obrigatorio !!}</label>
                        <select id="vinculo" name="vinculo" x-model="vinculo" required
                                @error('vinculo') aria-invalid="true" aria-describedby="erro-vinculo" @enderror
                                class="{{ $inputClasse }}">
                            <option value="">Selecione...</option>
                            @foreach (Manifestacao::VINCULOS as $vinculo)
                                <option value="{{ $vinculo }}">{{ $vinculo }}</option>
                            @endforeach
                        </select>
                        @error('vinculo') <p id="erro-vinculo" class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <p x-show="!vinculo" class="mt-4 rounded-md bg-slate-50 border border-dashed border-slate-200 px-4 py-3 text-sm text-slate-500">
                        Selecione o vínculo para exibir os campos de identificação correspondentes.
                    </p>

                    <div x-show="vinculo" x-cloak aria-live="polite" class="mt-6 grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-5">
                        @foreach ($camposIdentificacao as $campo => $def)
                            <div x-show="exibe('{{ $campo }}')" x-cloak class="{{ $def['col'] ?? '' }}">
                                <label for="{{ $campo }}" class="{{ $labelClasse }}">
                                    <span x-text="rotulo('{{ $campo }}')">{{ Manifestacao::ROTULOS_CAMPOS[$campo] }}</span> {!! $obrigatorio !!}
                                </label>
                                @if ($def['tipo'] === 'select')
                                    <select id="{{ $campo }}" name="{{ $campo }}" x-model="valores.{{ $campo }}" required
                                            :disabled="!exibe('{{ $campo }}')"
                                            @error($campo) aria-invalid="true" aria-describedby="erro-{{ $campo }}" @enderror
                                            class="{{ $inputClasse }}">
                                        <option value="">Selecione...</option>
                                        @foreach ($def['opcoes'] as $opcao)
                                            <option value="{{ $opcao }}">{{ $opcao }}</option>
                                        @endforeach
                                    </select>
                                @else
                                    <input type="{{ $def['tipo'] }}" id="{{ $campo }}" name="{{ $campo }}" required
                                           x-model="valores.{{ $campo }}"
                                           :disabled="!exibe('{{ $campo }}')"
                                           @isset($def['mascara']) @input="{{ $def['mascara'] }}" @endisset
                                           @isset($def['inputmode']) inputmode="{{ $def['inputmode'] }}" @endisset
                                           @isset($def['autocomplete']) autocomplete="{{ $def['autocomplete'] }}" @endisset
                                           @isset($def['placeholder']) placeholder="{{ $def['placeholder'] }}" @endisset
                                           @if ($campo === 'cpf')
                                               :placeholder="documentoCnpj ? '000.000.000-00 ou 00.000.000/0000-00' : '000.000.000-00'"
                                               :maxlength="documentoCnpj ? 18 : 14"
                                           @else
                                               maxlength="{{ $def['maxlength'] ?? 255 }}"
                                           @endif
                                           @error($campo) aria-invalid="true" aria-describedby="erro-{{ $campo }}" @enderror
                                           class="{{ $inputClasse }}">
                                @endif
                                @error($campo) <p id="erro-{{ $campo }}" class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>
                        @endforeach
                    </div>
                </fieldset>

                {{-- 2. Dados da manifestação --}}
                <fieldset class="{{ $secaoClasse }}">
                    <legend class="{{ $legendaClasse }}">2. Dados da manifestação</legend>

                    <fieldset class="mt-2 mb-6">
                        <legend class="{{ $labelClasse }}">Tipo de Manifestação {!! $obrigatorio !!}</legend>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            @foreach (Manifestacao::TIPOS as $tipo)
                                <label class="flex items-center gap-3 border border-slate-200 rounded-md px-4 py-3 cursor-pointer hover:border-indigo-400 has-[:checked]:border-indigo-500 has-[:checked]:ring-1 has-[:checked]:ring-indigo-500 transition">
                                    <input type="radio" name="tipo" value="{{ $tipo }}" @checked(old('tipo') === $tipo) required
                                           class="text-indigo-600 focus:ring-indigo-500">
                                    <span class="text-sm text-slate-700">{{ $tipo }}</span>
                                </label>
                            @endforeach
                        </div>
                        @error('tipo') <p class="mt-2 text-xs text-red-600">{{ $message }}</p> @enderror
                    </fieldset>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-5">
                        <div>
                            <label for="area_envolvida" class="{{ $labelClasse }}">Área Envolvida {!! $obrigatorio !!}</label>
                            <input type="text" id="area_envolvida" name="area_envolvida" value="{{ old('area_envolvida') }}" required maxlength="255"
                                   placeholder="Ex.: Secretaria, Financeiro" class="{{ $inputClasse }}">
                            @error('area_envolvida') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="assunto" class="{{ $labelClasse }}">Assunto {!! $obrigatorio !!}</label>
                            <input type="text" id="assunto" name="assunto" value="{{ old('assunto') }}" required maxlength="255" class="{{ $inputClasse }}">
                            @error('assunto') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="data_ocorrido" class="{{ $labelClasse }}">Data do Ocorrido {!! $obrigatorio !!}</label>
                            <input type="date" id="data_ocorrido" name="data_ocorrido" value="{{ old('data_ocorrido') }}" max="{{ now()->format('Y-m-d') }}" required class="{{ $inputClasse }}">
                            @error('data_ocorrido') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="local_ocorrido" class="{{ $labelClasse }}">Local do Ocorrido {!! $obrigatorio !!}</label>
                            <input type="text" id="local_ocorrido" name="local_ocorrido" value="{{ old('local_ocorrido') }}" required maxlength="255" class="{{ $inputClasse }}">
                            @error('local_ocorrido') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    {{-- Pessoas envolvidas --}}
                    <fieldset class="mt-6">
                        <legend class="{{ $labelClasse }}">Há pessoas envolvidas nesta manifestação? {!! $obrigatorio !!}</legend>
                        <div class="flex flex-wrap gap-3">
                            @foreach (['1' => 'Sim', '0' => 'Não'] as $valor => $rotulo)
                                <label class="flex items-center gap-2 border border-slate-200 rounded-md px-5 py-2.5 cursor-pointer hover:border-indigo-400 has-[:checked]:border-indigo-500 has-[:checked]:ring-1 has-[:checked]:ring-indigo-500 transition">
                                    <input type="radio" name="possui_pessoas_envolvidas" value="{{ $valor }}" x-model="valores.possui_pessoas_envolvidas" required
                                           aria-controls="bloco-pessoas-envolvidas" class="text-indigo-600 focus:ring-indigo-500">
                                    <span class="text-sm text-slate-700">{{ $rotulo }}</span>
                                </label>
                            @endforeach
                        </div>
                        @error('possui_pessoas_envolvidas') <p class="mt-2 text-xs text-red-600">{{ $message }}</p> @enderror

                        <div id="bloco-pessoas-envolvidas" x-show="valores.possui_pessoas_envolvidas === '1'" x-cloak x-transition.opacity aria-live="polite" class="mt-4">
                            <label for="pessoas_envolvidas" class="{{ $labelClasse }}">Pessoas envolvidas {!! $obrigatorio !!}</label>
                            <textarea id="pessoas_envolvidas" name="pessoas_envolvidas" rows="3" required maxlength="2000"
                                      x-model="valores.pessoas_envolvidas"
                                      :disabled="valores.possui_pessoas_envolvidas !== '1'"
                                      placeholder="Informe o nome das pessoas envolvidas e, se necessário, cargo, setor ou outras informações relevantes."
                                      class="{{ $inputClasse }} resize-y"></textarea>
                            @error('pessoas_envolvidas') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                    </fieldset>

                    <div class="mt-6">
                        <label for="descricao" class="{{ $labelClasse }}">Descrição Detalhada da Manifestação {!! $obrigatorio !!}</label>
                        <textarea id="descricao" name="descricao" rows="5" placeholder="Descreva detalhadamente sua manifestação..."
                                  x-model="valores.descricao" required minlength="10" maxlength="2000" aria-describedby="ajuda-descricao"
                                  class="{{ $inputClasse }} resize-y"></textarea>
                        <div class="mt-2 flex items-start justify-between gap-4">
                            <p id="ajuda-descricao" class="text-xs text-slate-500">Seja o mais específico possível, incluindo datas, locais e nomes quando relevante.</p>
                            <span class="text-xs text-slate-400 shrink-0" x-text="valores.descricao.length + '/2000'"></span>
                        </div>
                        @error('descricao') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    {{-- Anexos --}}
                    <div class="mt-6">
                        <p class="{{ $labelClasse }}">Anexos <span class="font-normal text-slate-500">(opcional)</span></p>
                        <label for="anexos"
                               @dragover.prevent="arrastando = true" @dragleave.prevent="arrastando = false"
                               @drop.prevent="soltarArquivos($event)"
                               :class="arrastando ? 'border-indigo-500 bg-indigo-50' : 'border-slate-300'"
                               class="flex flex-col items-center justify-center gap-2 border-2 border-dashed rounded-lg px-4 py-8 sm:py-10 cursor-pointer hover:border-indigo-400 focus-within:border-indigo-500 transition text-center">
                            <svg class="w-7 h-7 text-slate-400" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v12m0 0l-4-4m4 4l4-4M5 21h14" />
                            </svg>
                            <span class="text-sm text-slate-500">Clique para selecionar arquivos ou arraste aqui</span>
                            <span class="text-xs text-slate-400">PDF, JPG, PNG até 10MB cada (máximo 10 arquivos)</span>
                            <input type="file" id="anexos" name="anexos[]" multiple accept=".pdf,.jpg,.jpeg,.png"
                                   x-ref="inputAnexos" @change="selecionarArquivos($event)" class="sr-only">
                        </label>
                        <template x-if="arquivos.length">
                            <ul class="mt-3 space-y-2">
                                <template x-for="(arq, idx) in arquivos" :key="idx">
                                    <li class="flex items-center justify-between gap-3 rounded-md border border-slate-200 bg-slate-50 px-3 py-2 text-xs">
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
                        @error('anexos') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        @error('anexos.*') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                </fieldset>

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
        function formManifestacao(config) {
            return {
                vinculo: config.valores.vinculo,
                valores: config.valores,
                ciente: false,
                arquivos: [],
                arrastando: false,
                get documentoCnpj() {
                    return this.vinculo === 'Comunidade externa';
                },
                exibe(campo) {
                    return (config.campos[this.vinculo] || []).includes(campo);
                },
                rotulo(campo) {
                    return (config.rotulosPorVinculo[this.vinculo] || {})[campo] || config.rotulos[campo];
                },
                mascaraTelefone() {
                    const v = this.valores.telefone.replace(/\D/g, '').slice(0, 11);
                    if (v.length > 10) {
                        this.valores.telefone = `(${v.slice(0, 2)})${v.slice(2, 7)}-${v.slice(7)}`;
                    } else if (v.length > 6) {
                        this.valores.telefone = `(${v.slice(0, 2)})${v.slice(2, 6)}-${v.slice(6)}`;
                    } else if (v.length > 2) {
                        this.valores.telefone = `(${v.slice(0, 2)})${v.slice(2)}`;
                    } else {
                        this.valores.telefone = v;
                    }
                },
                mascaraDocumento() {
                    let v = this.valores.cpf.replace(/\D/g, '').slice(0, this.documentoCnpj ? 14 : 11);
                    if (v.length > 11) {
                        v = v.replace(/^(\d{2})(\d)/, '$1.$2')
                            .replace(/^(\d{2})\.(\d{3})(\d)/, '$1.$2.$3')
                            .replace(/\.(\d{3})(\d)/, '.$1/$2')
                            .replace(/(\d{4})(\d)/, '$1-$2');
                    } else {
                        v = v.replace(/(\d{3})(\d)/, '$1.$2')
                            .replace(/(\d{3})(\d)/, '$1.$2')
                            .replace(/(\d{3})(\d{1,2})$/, '$1-$2');
                    }
                    this.valores.cpf = v;
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
