<?php

namespace App\Jobs;

use App\Services\ReclameAqui\ReclameAquiSyncService;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Sincroniza as reclamações da RA API em segundo plano.
 *
 * ShouldBeUnique evita execuções sobrepostas quando disparado pelo Scheduler.
 */
class SincronizarReclameAquiJob implements ShouldBeUnique, ShouldQueue
{
    use Queueable;

    /**
     * Número de tentativas antes de marcar o job como falho.
     */
    public int $tries = 3;

    /**
     * Backoff exponencial (em segundos) entre as tentativas.
     *
     * @return array<int, int>
     */
    public function backoff(): array
    {
        return [1, 5, 10];
    }

    public function handle(ReclameAquiSyncService $service): void
    {
        $service->sincronizar();
    }

    public function failed(?Throwable $e): void
    {
        Log::error('Job de sincronização da RA API falhou.', [
            'mensagem' => $e?->getMessage(),
        ]);
    }
}
