<?php

namespace App\Providers;

use App\Notifications\ManifestacaoEncaminhadaNotification;
use App\Services\ComunicacaoInterna\Contracts\ComunicacaoInternaInterface;
use App\Services\ComunicacaoInterna\LocalComunicacaoInterna;
use App\Services\ReclameAqui\Contracts\ReclameAquiClientInterface;
use App\Services\ReclameAqui\NullReclameAquiClient;
use App\Services\ReclameAqui\ReclameAquiHttpClient;
use Illuminate\Notifications\Events\NotificationSent;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Ponto único de troca da integração com a RA API: enquanto não houver
        // credenciais/flag habilitada, resolve o cliente nulo (fallback seguro).
        $this->app->bind(ReclameAquiClientInterface::class, function () {
            $config = config('services.reclame_aqui');

            if (! ($config['enabled'] ?? false) || empty($config['base_url'])) {
                return new NullReclameAquiClient;
            }

            return new ReclameAquiHttpClient($config);
        });

        // Canal da comunicação interna dos encaminhamentos.
        // TODO(graph): resolver GraphComunicacaoInterna quando o driver for 'graph'.
        $this->app->bind(ComunicacaoInternaInterface::class, LocalComunicacaoInterna::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Event::listen(function (NotificationSent $event) {
            if ($event->channel === 'mail' && $event->notification instanceof ManifestacaoEncaminhadaNotification) {
                $event->notification->encaminhamento->forceFill(['email_enviado_em' => now()])->save();
            }
        });
    }
}
