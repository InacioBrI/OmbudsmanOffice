<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateManifestacaoRequest;
use App\Models\Manifestacao;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ManifestacaoController extends Controller
{
    public function index(Request $request): View
    {
        $manifestacoes = $this->filtrar($request)->paginate(15)->withQueryString();

        return view('Admin.manifestacoes.index', [
            'manifestacoes' => $manifestacoes,
            'titulo' => 'Painel principal',
            'secao' => 'painel',
        ]);
    }

    public function atendimentos(Request $request): View
    {
        $manifestacoes = $this->filtrar($request)->emAndamento()->paginate(15)->withQueryString();

        return view('Admin.manifestacoes.index', [
            'manifestacoes' => $manifestacoes,
            'titulo' => 'Atendimentos',
            'secao' => 'atendimentos',
        ]);
    }

    public function historico(Request $request): View
    {
        $manifestacoes = $this->filtrar($request)->encerradas()->paginate(15)->withQueryString();

        return view('Admin.manifestacoes.index', [
            'manifestacoes' => $manifestacoes,
            'titulo' => 'Histórico de manifestação',
            'secao' => 'historico',
        ]);
    }

    public function show(Manifestacao $manifestacao): View
    {
        $manifestacao->load('historicos');

        return view('Admin.manifestacoes.show', [
            'manifestacao' => $manifestacao,
            'titulo' => 'Detalhe da manifestação',
            'secao' => 'painel',
        ]);
    }

    public function update(UpdateManifestacaoRequest $manifestacaoRequest, Manifestacao $manifestacao)
    {
        $dados = $manifestacaoRequest->validated();
        $observacao = $dados['observacao'] ?? null;
        unset($dados['observacao']);

        $statusAnterior = $manifestacao->status;
        $statusMudou = $statusAnterior !== $dados['status'];

        if (! empty($dados['resposta']) || in_array($dados['status'], ['respondida', 'concluida'], true)) {
            $dados['respondido_em'] = now();
        }

        $manifestacao->update($dados);

        if ($statusMudou) {
            $manifestacao->registrarHistorico($statusAnterior, $dados['status'], $observacao);
        }

        return redirect()
            ->route('admin.manifestacoes.show', $manifestacao)
            ->with('sucesso', 'Manifestação atualizada com sucesso.');
    }

    public function destroy(Manifestacao $manifestacao)
    {
        foreach ($manifestacao->anexos ?? [] as $anexo) {
            if (! empty($anexo['path'])) {
                Storage::disk('public')->delete($anexo['path']);
            }
        }

        $manifestacao->delete();

        return redirect()
            ->route('admin.manifestacoes.index')
            ->with('sucesso', 'Manifestação excluída com sucesso.');
    }

    private function filtrar(Request $request): Builder
    {
        return Manifestacao::query()
            ->when($request->filled('busca'), function (Builder $query) use ($request) {
                $busca = $request->string('busca');
                $query->where(function (Builder $q) use ($busca) {
                    $q->where('nome', 'like', "%{$busca}%")
                        ->orWhere('protocolo', 'like', "%{$busca}%")
                        ->orWhere('cpf', 'like', "%{$busca}%")
                        ->orWhere('assunto', 'like', "%{$busca}%")
                        ->orWhere('descricao', 'like', "%{$busca}%");
                });
            })
            ->when($request->filled('categoria'), fn (Builder $q) => $q->where('tipo', $request->string('categoria')))
            ->when($request->filled('status'), fn (Builder $q) => $q->where('status', $request->string('status')))
            ->when($request->filled('data'), fn (Builder $q) => $q->whereDate('created_at', $request->date('data')))
            ->latest();
    }
}
