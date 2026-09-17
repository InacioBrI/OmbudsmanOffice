<?php

namespace App\Services\ReclameAqui\DTO;

use App\Models\ReclameAquiManifestacao;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;

/**
 * Representação normalizada de uma reclamação recebida da RA API.
 *
 * Isola o formato cru da API do restante da aplicação: a normalização
 * acontece em um único ponto (fromApiResponse), facilitando o ajuste dos
 * campos quando a documentação oficial estiver disponível.
 */
final readonly class ReclamacaoDTO
{
    /**
     * @param  array<string, mixed>  $payload  Resposta bruta da API (auditoria).
     */
    public function __construct(
        public string $externalId,
        public ?string $protocolo,
        public ?string $titulo,
        public ?string $descricao,
        public ?string $categoria,
        public ?string $consumidorNome,
        public ?string $consumidorLocal,
        public ?string $statusExterno,
        public ?string $resposta,
        public ?Carbon $respondidoEm,
        public ?string $url,
        public ?string $avaliacao,
        public ?Carbon $publicadoEm,
        public array $payload,
    ) {}

    /**
     * Cria o DTO a partir de um item cru da RA API.
     *
     * TODO(reclame-aqui): ajustar as chaves de acesso ($item['...']) conforme
     * o schema real da documentação oficial. As chaves abaixo são placeholders
     * genéricos e NÃO representam campos reais da API.
     *
     * @param  array<string, mixed>  $item
     */
    public static function fromApiResponse(array $item): self
    {
        return new self(
            externalId: (string) Arr::get($item, 'id'),
            protocolo: Arr::get($item, 'protocolo'),
            titulo: Arr::get($item, 'titulo'),
            descricao: Arr::get($item, 'descricao'),
            categoria: Arr::get($item, 'categoria'),
            consumidorNome: Arr::get($item, 'consumidor.nome'),
            consumidorLocal: Arr::get($item, 'consumidor.local'),
            statusExterno: Arr::get($item, 'status'),
            resposta: Arr::get($item, 'resposta'),
            respondidoEm: self::parseData(Arr::get($item, 'respondido_em')),
            url: Arr::get($item, 'url'),
            avaliacao: Arr::get($item, 'avaliacao'),
            publicadoEm: self::parseData(Arr::get($item, 'publicado_em')),
            payload: $item,
        );
    }

    /**
     * Converte o DTO em atributos prontos para persistência no Model.
     *
     * @return array<string, mixed>
     */
    public function toAttributes(): array
    {
        return [
            'external_id' => $this->externalId,
            'protocolo' => $this->protocolo,
            'titulo' => $this->titulo,
            'descricao' => $this->descricao,
            'categoria' => $this->categoria,
            'consumidor_nome' => $this->consumidorNome,
            'consumidor_local' => $this->consumidorLocal,
            'status_externo' => $this->statusExterno,
            'status' => ReclameAquiManifestacao::mapearStatus($this->statusExterno),
            'resposta' => $this->resposta,
            'respondido_em' => $this->respondidoEm,
            'url' => $this->url,
            'avaliacao' => $this->avaliacao,
            'publicado_em' => $this->publicadoEm,
            'payload' => $this->payload,
            'sincronizado_em' => now(),
        ];
    }

    private static function parseData(mixed $valor): ?Carbon
    {
        if (empty($valor)) {
            return null;
        }

        try {
            return Carbon::parse($valor);
        } catch (\Throwable) {
            return null;
        }
    }
}
