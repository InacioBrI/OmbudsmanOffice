<?php

namespace App\Services\ReclameAqui;

use App\Exceptions\ReclameAquiException;
use App\Models\ReclameAquiManifestacao;
use App\Services\ReclameAqui\Contracts\ReclameAquiClientInterface;
use App\Services\ReclameAqui\DTO\ReclamacaoDTO;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Orquestra a sincronização das reclamações da RA API para a base local.
 *
 * Responsabilidades centralizadas aqui: buscar via Client, normalizar em DTOs,
 * gravar deduplicando por external_id (cria ou atualiza), tratar erros e logar.
 */
class ReclameAquiSyncService
{
    public function __construct(private readonly ReclameAquiClientInterface $client) {}

    /**
     * Executa a sincronização e retorna um resumo do resultado.
     *
     * @param  array<string, mixed>  $filtros
     * @return array{criadas: int, atualizadas: int, falhas: int}
     */
    public function sincronizar(array $filtros = []): array
    {
        $resumo = ['criadas' => 0, 'atualizadas' => 0, 'falhas' => 0];

        try {
            $itens = $this->client->listarReclamacoes($filtros);
        } catch (ReclameAquiException $e) {
            Log::error('Falha ao listar reclamações da RA API.', [
                'mensagem' => $e->getMessage(),
                'contexto' => $e->context(),
            ]);

            throw $e;
        }

        foreach ($itens as $item) {
            try {
                $this->persistir(ReclamacaoDTO::fromApiResponse($item), $resumo);
            } catch (Throwable $e) {
                $resumo['falhas']++;
                Log::warning('Falha ao processar reclamação da RA API.', [
                    'mensagem' => $e->getMessage(),
                    'external_id' => $item['id'] ?? null,
                ]);
            }
        }

        Log::info('Sincronização da RA API concluída.', $resumo);

        return $resumo;
    }

    /**
     * @param  array{criadas: int, atualizadas: int, falhas: int}  $resumo
     */
    private function persistir(ReclamacaoDTO $dto, array &$resumo): void
    {
        $existente = ReclameAquiManifestacao::where('external_id', $dto->externalId)->exists();

        ReclameAquiManifestacao::updateOrCreate(
            ['external_id' => $dto->externalId],
            $dto->toAttributes(),
        );

        $existente ? $resumo['atualizadas']++ : $resumo['criadas']++;
    }
}
