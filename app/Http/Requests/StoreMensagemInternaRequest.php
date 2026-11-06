<?php

namespace App\Http\Requests;

use App\Models\ManifestacaoMensagemInterna;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMensagemInternaRequest extends FormRequest
{
    protected $errorBag = 'mensagem';

    // TODO(sso): restringir à Ouvidoria e ao setor de destino quando houver autenticação.
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
            'corpo' => ['required', 'string', 'max:5000'],
            'remetente_tipo' => ['required', Rule::in(ManifestacaoMensagemInterna::REMETENTES)],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'corpo.required' => 'Escreva a mensagem.',
            'corpo.max' => 'A mensagem deve ter no máximo 5000 caracteres.',
            'remetente_tipo.in' => 'Remetente inválido.',
        ];
    }
}
