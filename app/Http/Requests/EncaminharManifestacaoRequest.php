<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EncaminharManifestacaoRequest extends FormRequest
{
    protected $errorBag = 'encaminhamento';

    // TODO(sso): restringir ao perfil Ouvidoria quando houver autenticação.
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'compartilhar_dados' => $this->boolean('compartilhar_dados'),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'setor_destino_id' => [
                'required',
                'integer',
                Rule::exists('setores', 'id')->where('ativo', 1)->where('is_ouvidoria', 0),
            ],
            'motivo' => ['required', 'string', 'min:10', 'max:2000'],
            'compartilhar_dados' => ['boolean'],
            'confirmacao_compartilhamento' => ['accepted_if:compartilhar_dados,true'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'setor_destino_id.required' => 'Selecione o setor de destino.',
            'setor_destino_id.exists' => 'Setor de destino inválido ou inativo.',
            'motivo.required' => 'Descreva o motivo do encaminhamento.',
            'motivo.min' => 'O motivo deve ter ao menos 10 caracteres.',
            'motivo.max' => 'O motivo deve ter no máximo 2000 caracteres.',
            'confirmacao_compartilhamento.accepted_if' => 'Confirme o compartilhamento dos dados do aluno antes de encaminhar.',
        ];
    }
}
