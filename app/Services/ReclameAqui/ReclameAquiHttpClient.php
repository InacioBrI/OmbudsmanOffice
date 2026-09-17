<?php

namespace App\Services\ReclameAqui;

use App\Exceptions\ReclameAquiException;
use App\Services\ReclameAqui\Contracts\ReclameAquiClientInterface;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

/**
 * Implementação HTTP concreta da comunicação com a RA API (Reclame AQUI).
 *
 * Só é utilizada quando a integração está habilitada e configurada
 * (ver App\Providers\AppServiceProvider). Toda a configuração vem de
 * config('services.reclame_aqui').
 */
class ReclameAquiHttpClient implements ReclameAquiClientInterface
{
    /**
     * @param  array<string, mixed>  $config
     */
    public function __construct(private readonly array $config) {}

    public function listarReclamacoes(array $filtros = []): array
    {
        // TODO(reclame-aqui): ajustar o path e os parâmetros de query conforme
        // a documentação oficial (paginação, intervalo de datas, etc.).
        $resposta = $this->request()->get('reclamacoes', $filtros);

        if ($resposta->failed()) {
            throw ReclameAquiException::respostaInvalida('listagem de reclamações', [
                'status' => $resposta->status(),
            ]);
        }

        // TODO(reclame-aqui): ajustar a chave onde a coleção de itens vem
        // encapsulada na resposta (ex.: 'data', 'items', 'complaints'...).
        $dados = $resposta->json('data');

        if (! is_array($dados)) {
            throw ReclameAquiException::respostaInvalida('formato inesperado na listagem');
        }

        return $dados;
    }

    public function obterReclamacao(string $id): array
    {
        // TODO(reclame-aqui): ajustar o path do recurso individual.
        $resposta = $this->request()->get("reclamacoes/{$id}");

        if ($resposta->failed()) {
            throw ReclameAquiException::respostaInvalida('detalhe da reclamação', [
                'id' => $id,
                'status' => $resposta->status(),
            ]);
        }

        $dados = $resposta->json('data');

        if (! is_array($dados)) {
            throw ReclameAquiException::respostaInvalida('formato inesperado no detalhe', ['id' => $id]);
        }

        return $dados;
    }

    /**
     * Monta um cliente HTTP configurado (base URL, timeouts, retry e auth).
     */
    private function request(): PendingRequest
    {
        try {
            return Http::baseUrl((string) $this->config['base_url'])
                ->timeout((int) $this->config['timeout'])
                ->connectTimeout((int) $this->config['connect_timeout'])
                ->retry((int) $this->config['retry'], 200)
                ->acceptJson()
                ->withHeaders($this->authHeaders());
        } catch (ConnectionException $e) {
            throw ReclameAquiException::comunicacao($e->getMessage(), $e);
        }
    }

    /**
     * Cabeçalhos de autenticação da RA API.
     *
     * TODO(reclame-aqui): implementar a autenticação OAuth2 real (obter token
     * via client_id/client_secret e enviá-lo como Bearer). Por enquanto,
     * apenas repassa um token estático de config, se existir — sem lógica de
     * renovação/refresh.
     *
     * @return array<string, string>
     */
    private function authHeaders(): array
    {
        $token = $this->config['token'] ?? null;

        return $token ? ['Authorization' => "Bearer {$token}"] : [];
    }
}
