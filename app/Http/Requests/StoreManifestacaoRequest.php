<?php

namespace App\Http\Requests;

use App\Models\Manifestacao;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreManifestacaoRequest extends FormRequest
{
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
            // Dados do manifestante
            'nome' => ['required', 'string', 'max:255'],
            'cpf' => ['required', 'string', 'regex:/^\d{3}\.?\d{3}\.?\d{3}-?\d{2}$/'],
            'rm' => ['required', 'string', 'max:50'],
            'vinculo' => ['required', Rule::in(Manifestacao::VINCULOS)],
            'curso' => ['required', 'string', 'max:255'],
            'unidade' => ['required', 'string', 'max:255'],
            'semestre' => ['required', 'string', 'max:50'],
            'email' => ['required', 'email', 'max:255'],
            'telefone' => ['required', 'string', 'max:30'],

            // Dados da manifestação
            'tipo' => ['required', Rule::in(Manifestacao::TIPOS)],
            'area_envolvida' => ['required', 'string', 'max:255'],
            'assunto' => ['required', 'string', 'max:255'],
            'descricao' => ['required', 'string', 'min:10'],
            'data_ocorrido' => ['required', 'date', 'before_or_equal:today'],
            'local_ocorrido' => ['required', 'string', 'max:255'],
            'pessoas_envolvidas' => ['required', 'string'],

            'ciente' => ['accepted'],
            'anexos' => ['nullable', 'array', 'max:10'],
            'anexos.*' => ['file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'nome.required' => 'Informe o nome completo.',
            'cpf.required' => 'Informe o CPF.',
            'cpf.regex' => 'CPF inválido.',
            'rm.required' => 'Informe o RM.',
            'vinculo.required' => 'Selecione o tipo de vínculo.',
            'vinculo.in' => 'Tipo de vínculo inválido.',
            'curso.required' => 'Informe o curso.',
            'unidade.required' => 'Informe a unidade.',
            'semestre.required' => 'Informe o semestre.',
            'email.required' => 'Informe o e-mail.',
            'email.email' => 'Informe um e-mail válido.',
            'telefone.required' => 'Informe o telefone.',
            'tipo.required' => 'Selecione o tipo de manifestação.',
            'tipo.in' => 'Tipo de manifestação inválido.',
            'area_envolvida.required' => 'Informe a área envolvida.',
            'assunto.required' => 'Informe o assunto.',
            'descricao.required' => 'Descreva sua manifestação.',
            'descricao.min' => 'A descrição deve ter ao menos 10 caracteres.',
            'data_ocorrido.required' => 'Informe a data do ocorrido.',
            'data_ocorrido.before_or_equal' => 'A data do ocorrido não pode ser futura.',
            'local_ocorrido.required' => 'Informe o local do ocorrido.',
            'pessoas_envolvidas.required' => 'Informe as pessoas envolvidas.',
            'ciente.accepted' => 'É necessário aceitar o termo para prosseguir.',
            'anexos.*.mimes' => 'Anexos devem ser PDF, JPG ou PNG.',
            'anexos.*.max' => 'Cada anexo deve ter no máximo 10MB.',
        ];
    }
}
