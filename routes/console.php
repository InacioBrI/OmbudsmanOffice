<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Sincronização automática da RA API. Só agenda quando a integração está
// habilitada (config/services.php) — inativo até termos credenciais.
if (config('services.reclame_aqui.enabled')) {
    Schedule::command('reclame-aqui:sincronizar')
        ->hourly()
        ->withoutOverlapping();
}
