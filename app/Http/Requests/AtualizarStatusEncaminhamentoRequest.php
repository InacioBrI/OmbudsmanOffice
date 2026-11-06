<?php

namespace App\Http\Requests;

use App\Models\ManifestacaoEncaminhamento;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AtualizarStatusEncaminhamentoRequest extends FormRequest
{
    // TODO(sso): restringir conforme perfil quando houver autenticação.
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
            'status' => ['required', Rule::in(array_keys(ManifestacaoEncaminhamento::STATUSES))],
        ];
    }
}
