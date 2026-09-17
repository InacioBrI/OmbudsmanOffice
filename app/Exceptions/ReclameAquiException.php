<?php

namespace App\Exceptions;

use RuntimeException;
use Throwable;

/**
 * Exceção de domínio para falhas na integração com a RA API (Reclame AQUI):
 * erros de comunicação, timeout ou respostas inválidas.
 */
class ReclameAquiException extends RuntimeException
{
    /**
     * @param  array<string, mixed>  $contexto
     */
    public function __construct(
        string $message = '',
        protected array $contexto = [],
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, 0, $previous);
    }

    /**
     * Contexto estruturado anexado aos logs deste erro.
     *
     * @return array<string, mixed>
     */
    public function context(): array
    {
        return $this->contexto;
    }

    public static function comunicacao(string $mensagem, ?Throwable $previous = null): self
    {
        return new self("Falha de comunicação com a RA API: {$mensagem}", [], $previous);
    }

    /**
     * @param  array<string, mixed>  $contexto
     */
    public static function respostaInvalida(string $mensagem, array $contexto = []): self
    {
        return new self("Resposta inválida da RA API: {$mensagem}", $contexto);
    }
}
