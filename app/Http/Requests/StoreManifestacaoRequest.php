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
            'tipo' => ['required', Rule::in(Manifestacao::TIPOS)],
            'nome' => ['nullable', 'string', 'max:255'],
            'rm' => ['nullable', 'string', 'max:50'],
            'telefone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:255'],
            'descricao' => ['required', 'string', 'min:10'],
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
            'tipo.required' => 'Selecione o tipo de manifestação.',
            'tipo.in' => 'Tipo de manifestação inválido.',
            'descricao.required' => 'Descreva sua manifestação.',
            'descricao.min' => 'A descrição deve ter ao menos 10 caracteres.',
            'ciente.accepted' => 'É necessário aceitar o termo para prosseguir.',
            'anexos.*.mimes' => 'Anexos devem ser PDF, JPG ou PNG.',
            'anexos.*.max' => 'Cada anexo deve ter no máximo 10MB.',
        ];
    }
}
