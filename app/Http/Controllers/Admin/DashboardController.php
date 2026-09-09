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

        return view('Admin.dashboard', [
            'total' => Manifestacao::count(),
            'emAndamento' => Manifestacao::emAndamento()->count(),
            'encerradas' => Manifestacao::encerradas()->count(),
            'porStatus' => $porStatus,
            'porCategoria' => $porCategoria,
            'titulo' => 'Dashboard',
            'secao' => 'dashboard',
        ]);
    }
}
