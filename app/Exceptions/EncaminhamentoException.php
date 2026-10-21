<?php

namespace App\Exceptions;

use DomainException;

/**
 * Regras de negócio violadas no encaminhamento interno. A mensagem é exibida ao usuário do painel.
 */
class EncaminhamentoException extends DomainException
{
    public static function manifestacaoEncerrada(): self
    {
        return new self('Não é possível encaminhar ou movimentar uma manifestação encerrada.');
    }

    public static function duplicado(string $setor): self
    {
        return new self("Já existe um encaminhamento ativo desta manifestação para o setor {$setor}.");
    }

    public static function transicaoInvalida(): self
    {
        return new self('Alteração de status do encaminhamento não permitida.');
    }

    public static function encaminhamentoCancelado(): self
    {
        return new self('Não é possível enviar mensagens em um encaminhamento cancelado.');
    }
}
