<?php

namespace App\Http\Requests;

use App\Models\Manifestacao;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateManifestacaoRequest extends FormRequest
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
        /** @var Manifestacao $manifestacao */
        $manifestacao = $this->route('manifestacao');

        return [
            'status' => ['required', Rule::in($manifestacao->statusPermitidos())],
            'resposta' => ['nullable', 'string'],
            'observacao' => ['nullable', 'string', 'max:500'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'status.required' => 'Selecione um status.',
            'status.in' => 'Transição de status inválida para o fluxo atual.',
        ];
    }
}
