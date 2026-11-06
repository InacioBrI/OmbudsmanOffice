<?php

namespace App\Http\Requests;

use App\Models\Manifestacao;
use App\Rules\Cpf;
use App\Rules\CpfOuCnpj;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreManifestacaoRequest extends FormRequest
{
    public const TELEFONE_REGEX = '/^(\(\d{2}\)\s?\d{4,5}-?\d{4}|\d{10,11})$/';

    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'vinculo' => ['required', Rule::in(Manifestacao::VINCULOS)],

            // Dados do manifestante (somente os campos do vínculo selecionado)
            ...$this->regrasIdentificacao(),

            // Dados da manifestação
            'tipo' => ['required', Rule::in(Manifestacao::TIPOS)],
            'area_envolvida' => ['required', 'string', 'max:255'],
            'assunto' => ['required', 'string', 'max:255'],
            'descricao' => ['required', 'string', 'min:10', 'max:2000'],
            'data_ocorrido' => ['required', 'date', 'before_or_equal:today'],
            'local_ocorrido' => ['required', 'string', 'max:255'],
            'possui_pessoas_envolvidas' => ['required', 'boolean'],
            'pessoas_envolvidas' => ['exclude_unless:possui_pessoas_envolvidas,1', 'required', 'string', 'max:2000'],

            'ciente' => ['accepted'],
            'anexos' => ['nullable', 'array', 'max:10'],
            'anexos.*' => ['file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function regrasIdentificacao(): array
    {
        $vinculo = $this->input('vinculo');

        $regras = [
            'nome' => ['required', 'string', 'max:255'],
            'cpf' => ['required', 'string', $vinculo === 'Comunidade externa' ? new CpfOuCnpj : new Cpf],
            'rm' => ['required', 'string', 'max:50'],
            'ra' => ['required', 'string', 'max:50'],
            'aluno_nome' => ['required', 'string', 'max:255'],
            'organizacao' => ['required', 'string', 'max:255'],
            'curso' => ['required', 'string', 'max:255'],
            'semestre' => ['required', Rule::in(Manifestacao::semestres())],
            'unidade' => ['required', Rule::in(Manifestacao::UNIDADES)],
            'telefone' => ['required', 'string', 'regex:'.self::TELEFONE_REGEX],
            'email' => ['required', 'email', 'max:255'],
        ];

        return array_intersect_key($regras, array_flip(Manifestacao::CAMPOS_POR_VINCULO[$vinculo] ?? []));
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            ...array_map('mb_strtolower', Manifestacao::rotulosCampos($this->input('vinculo'))),
            'tipo' => 'tipo de manifestação',
            'area_envolvida' => 'área envolvida',
            'assunto' => 'assunto',
            'descricao' => 'descrição',
            'data_ocorrido' => 'data do ocorrido',
            'local_ocorrido' => 'local do ocorrido',
            'pessoas_envolvidas' => 'pessoas envolvidas',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'required' => 'O campo :attribute é obrigatório.',
            'max' => 'O campo :attribute deve ter no máximo :max caracteres.',
            'in' => 'Selecione uma opção válida para :attribute.',
            'email' => 'Informe um e-mail válido.',
            'telefone.regex' => 'Informe o telefone no formato (00)00000-0000.',
            'vinculo.required' => 'Selecione o tipo de vínculo.',
            'vinculo.in' => 'Tipo de vínculo inválido.',
            'tipo.required' => 'Selecione o tipo de manifestação.',
            'descricao.required' => 'Descreva sua manifestação.',
            'descricao.min' => 'A descrição deve ter ao menos 10 caracteres.',
            'data_ocorrido.date' => 'Informe uma data válida.',
            'data_ocorrido.before_or_equal' => 'A data do ocorrido não pode ser futura.',
            'possui_pessoas_envolvidas.required' => 'Informe se há pessoas envolvidas nesta manifestação.',
            'possui_pessoas_envolvidas.boolean' => 'Informe se há pessoas envolvidas nesta manifestação.',
            'pessoas_envolvidas.required' => 'Informe as pessoas envolvidas.',
            'ciente.accepted' => 'É necessário aceitar o termo para prosseguir.',
            'anexos.max' => 'Envie no máximo 10 anexos.',
            'anexos.*.mimes' => 'Anexos devem ser PDF, JPG ou PNG.',
            'anexos.*.max' => 'Cada anexo deve ter no máximo 10MB.',
        ];
    }
}
