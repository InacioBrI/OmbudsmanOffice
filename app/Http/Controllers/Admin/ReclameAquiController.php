<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ReclameAquiManifestacao;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReclameAquiController extends Controller
{
    public function index(Request $request): View
    {
        $manifestacoes = $this->filtrar($request)->paginate(15)->withQueryString();

        return view('Admin.reclameAqui.index', [
            'manifestacoes' => $manifestacoes,
            'titulo' => 'Reclame Aqui',
            'secao' => 'reclame_aqui',
        ]);
    }

    private function filtrar(Request $request): Builder
    {
        return ReclameAquiManifestacao::query()
            ->when($request->filled('busca'), function (Builder $query) use ($request) {
                $busca = $request->string('busca');
                $query->where(function (Builder $q) use ($busca) {
                    $q->where('titulo', 'like', "%{$busca}%")
                        ->orWhere('protocolo', 'like', "%{$busca}%")
                        ->orWhere('consumidor_nome', 'like', "%{$busca}%")
                        ->orWhere('descricao', 'like', "%{$busca}%");
                });
            })
            ->when($request->filled('status'), fn (Builder $q) => $q->where('status', $request->string('status')))
            ->when($request->filled('data'), fn (Builder $q) => $q->whereDate('publicado_em', $request->date('data')))
            ->latest('publicado_em');
    }
}
