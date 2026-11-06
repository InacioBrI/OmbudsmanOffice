<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Manifestacao;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $porStatus = Manifestacao::selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $porCategoria = Manifestacao::selectRaw('tipo, count(*) as total')
            ->groupBy('tipo')
            ->pluck('total', 'tipo');

        $inicio = now()->startOfMonth()->subMonths(6);
        $contagensMensais = Manifestacao::query()
            ->selectRaw('DATE(created_at) as dia, status, count(*) as total')
            ->where('created_at', '>=', $inicio)
            ->where('created_at', '<', now()->startOfMonth()->addMonth())
            ->groupByRaw('DATE(created_at), status')
            ->get();

        $serieMensal = ['labels' => [], 'total' => [], 'emAndamento' => [], 'encerradas' => []];
        $indices = [];

        for ($i = 0; $i < 7; $i++) {
            $mes = $inicio->copy()->addMonths($i);
            $indices[$mes->format('Y-m')] = $i;
            $serieMensal['labels'][] = $mes->format('m/Y');
            $serieMensal['total'][] = 0;
            $serieMensal['emAndamento'][] = 0;
            $serieMensal['encerradas'][] = 0;
        }

        foreach ($contagensMensais as $contagem) {
            $indice = $indices[substr($contagem->dia, 0, 7)] ?? null;
            if ($indice === null) {
                continue;
            }

            $serieMensal['total'][$indice] += (int) $contagem->total;
            if (in_array($contagem->status, Manifestacao::EM_ANDAMENTO, true)) {
                $serieMensal['emAndamento'][$indice] += (int) $contagem->total;
            } elseif (in_array($contagem->status, Manifestacao::ENCERRADAS, true)) {
                $serieMensal['encerradas'][$indice] += (int) $contagem->total;
            }
        }

        return view('Admin.dashboard', [
            'total' => Manifestacao::count(),
            'emAndamento' => Manifestacao::emAndamento()->count(),
            'encerradas' => Manifestacao::encerradas()->count(),
            'porStatus' => $porStatus,
            'porCategoria' => $porCategoria,
            'serieMensal' => $serieMensal,
            'titulo' => 'Dashboard',
            'secao' => 'dashboard',
        ]);
    }
}
