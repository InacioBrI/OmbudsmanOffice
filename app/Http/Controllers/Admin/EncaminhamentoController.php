<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\EncaminhamentoException;
use App\Http\Controllers\Controller;
use App\Http\Requests\AtualizarStatusEncaminhamentoRequest;
use App\Http\Requests\EncaminharManifestacaoRequest;
use App\Models\Manifestacao;
use App\Models\ManifestacaoEncaminhamento;
use App\Models\Setor;
use App\Services\Encaminhamento\EncaminhamentoService;
use Illuminate\Http\RedirectResponse;

class EncaminhamentoController extends Controller
{
    public function __construct(private EncaminhamentoService $service) {}

    public function store(EncaminharManifestacaoRequest $request, Manifestacao $manifestacao): RedirectResponse
    {
        $destino = Setor::findOrFail($request->integer('setor_destino_id'));

        try {
            $this->service->encaminhar($manifestacao, $destino, $request->string('motivo')->trim()->toString(), $request->boolean('compartilhar_dados'));
        } catch (EncaminhamentoException $e) {
            return back()->withInput()->with('erro', $e->getMessage());
        }

        return redirect()
            ->route('admin.manifestacoes.show', $manifestacao)
            ->with('sucesso', "Manifestação encaminhada ao setor {$destino->nome}. O setor será notificado por e-mail.");
    }

    public function status(AtualizarStatusEncaminhamentoRequest $request, ManifestacaoEncaminhamento $encaminhamento): RedirectResponse
    {
        try {
            $this->service->atualizarStatus($encaminhamento, $request->string('status')->toString());
        } catch (EncaminhamentoException $e) {
            return back()->with('erro', $e->getMessage());
        }

        return redirect()
            ->route('admin.manifestacoes.show', $encaminhamento->manifestacao_id)
            ->with('sucesso', "Encaminhamento atualizado para \"{$encaminhamento->status_label}\".");
    }
}
