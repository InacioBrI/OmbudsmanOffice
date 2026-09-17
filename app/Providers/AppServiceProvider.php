<?php

namespace App\Providers;

use App\Services\ReclameAqui\Contracts\ReclameAquiClientInterface;
use App\Services\ReclameAqui\NullReclameAquiClient;
use App\Services\ReclameAqui\ReclameAquiHttpClient;
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
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
