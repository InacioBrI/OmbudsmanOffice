<?php

namespace App\Services\ReclameAqui;

use App\Services\ReclameAqui\Contracts\ReclameAquiClientInterface;
use Illuminate\Support\Facades\Log;

/**
 * Implementação nula usada como fallback enquanto a integração com a RA API
 * não está habilitada/configurada (sem credenciais).
 *
 * Não realiza nenhuma chamada externa: apenas retorna dados vazios e registra
 * um aviso. Isso mantém a aplicação funcional e evita respostas fictícias.
 */
class NullReclameAquiClient implements ReclameAquiClientInterface
{
    public function listarReclamacoes(array $filtros = []): array
    {
        $this->avisar();

        return [];
    }

    public function obterReclamacao(string $id): array
    {
        $this->avisar();

        return [];
    }

    private function avisar(): void
    {
        Log::warning('Integração com a RA API não configurada; usando NullReclameAquiClient.');
    }
}
