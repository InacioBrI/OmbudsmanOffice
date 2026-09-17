<?php

namespace App\Services\ReclameAqui\Contracts;

use App\Exceptions\ReclameAquiException;

/**
 * Contrato da comunicação com a RA API (Reclame AQUI).
 *
 * Mantém a integração desacoplada da aplicação: o restante do sistema depende
 * apenas desta interface, e a implementação concreta (HTTP real ou fallback
 * nulo) é resolvida via container no AppServiceProvider.
 *
 * As assinaturas são genéricas de propósito — os campos e endpoints reais
 * serão ajustados quando a documentação oficial estiver disponível.
 */
interface ReclameAquiClientInterface
{
    /**
     * Lista as reclamações disponíveis na RA API (capacidade de "Leitura").
     *
     * @param  array<string, mixed>  $filtros  Filtros opcionais (data, página, status...).
     * @return array<int, array<string, mixed>> Itens crus retornados pela API.
     *
     * @throws ReclameAquiException
     */
    public function listarReclamacoes(array $filtros = []): array;

    /**
     * Obtém os detalhes de uma reclamação específica.
     *
     * @return array<string, mixed> Item cru retornado pela API.
     *
     * @throws ReclameAquiException
     */
    public function obterReclamacao(string $id): array;

    // TODO(reclame-aqui): capacidade de "Resposta" (responder reclamação,
    // solicitar moderação/avaliação). Esboçar métodos como
    // responder(string $id, string $texto): array quando formos implementar.
}
