<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreManifestacaoRequest;
use App\Models\Manifestacao;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ManifestacaoController extends Controller
{
    public function create(): View
    {
        return view('Forms.index');
    }

    public function store(StoreManifestacaoRequest $request)
    {
        $dados = $request->safe()->except(['ciente', 'anexos']);
        $dados['protocolo'] = $this->gerarProtocolo();
        $dados['status'] = 'recebida';
        $dados['anexos'] = $this->salvarAnexos($request);

        $manifestacao = Manifestacao::create($dados);

        return redirect()
            ->route('manifestacoes.sucesso', $manifestacao->protocolo);
    }

    public function sucesso(string $protocolo): View
    {
        $manifestacao = Manifestacao::where('protocolo', $protocolo)->firstOrFail();

        return view('Forms.sucesso', compact('manifestacao'));
    }

    public function acompanhar(Request $request): View
    {
        $protocolo = $request->query('protocolo');
        $manifestacao = null;
        $naoEncontrado = false;

        if ($protocolo) {
            $manifestacao = Manifestacao::where('protocolo', $protocolo)->first();
            $naoEncontrado = $manifestacao === null;
        }

        return view('Forms.acompanhar', compact('manifestacao', 'protocolo', 'naoEncontrado'));
    }

    private function gerarProtocolo(): string
    {
        do {
            $protocolo = 'OUV-' . now()->format('Ymd') . '-' . strtoupper(Str::random(6));
        } while (Manifestacao::where('protocolo', $protocolo)->exists());

        return $protocolo;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function salvarAnexos(StoreManifestacaoRequest $request): array
    {
        $anexos = [];

        foreach ($request->file('anexos', []) as $arquivo) {
            $path = $arquivo->store('anexos', 'public');
            $anexos[] = [
                'path' => $path,
                'nome' => $arquivo->getClientOriginalName(),
                'tamanho' => $arquivo->getSize(),
            ];
        }

        return $anexos;
    }
}
