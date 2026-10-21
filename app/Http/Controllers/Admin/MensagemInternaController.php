<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\EncaminhamentoException;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMensagemInternaRequest;
use App\Models\ManifestacaoEncaminhamento;
use App\Services\Encaminhamento\EncaminhamentoService;
use Illuminate\Http\RedirectResponse;

class MensagemInternaController extends Controller
{
    public function __construct(private EncaminhamentoService $service) {}

    public function store(StoreMensagemInternaRequest $request, ManifestacaoEncaminhamento $encaminhamento): RedirectResponse
    {
        try {
            $this->service->enviarMensagem($encaminhamento, $request->string('remetente_tipo')->toString(), $request->string('corpo')->trim()->toString());
        } catch (EncaminhamentoException $e) {
            return back()->withInput()->with('erro', $e->getMessage());
        }

        return redirect()
            ->to(route('admin.manifestacoes.show', $encaminhamento->manifestacao_id)."#encaminhamento-{$encaminhamento->id}")
            ->with('sucesso', 'Mensagem registrada na comunicação interna.');
    }
}
