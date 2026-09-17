<?php

namespace App\Console\Commands;

use App\Jobs\SincronizarReclameAquiJob;
use App\Services\ReclameAqui\ReclameAquiSyncService;
use Illuminate\Console\Command;

class SincronizarReclameAquiCommand extends Command
{
    protected $signature = 'reclame-aqui:sincronizar {--sync : Executa a sincronização imediatamente, sem enfileirar}';

    protected $description = 'Sincroniza as reclamações da RA API (Reclame AQUI) para a base local';

    public function handle(ReclameAquiSyncService $service): int
    {
        if (! $this->option('sync')) {
            SincronizarReclameAquiJob::dispatch();
            $this->info('Job de sincronização da RA API enfileirado.');

            return self::SUCCESS;
        }

        $resumo = $service->sincronizar();

        $this->info(sprintf(
            'Sincronização concluída: %d criada(s), %d atualizada(s), %d falha(s).',
            $resumo['criadas'],
            $resumo['atualizadas'],
            $resumo['falhas'],
        ));

        return self::SUCCESS;
    }
}
