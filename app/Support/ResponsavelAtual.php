<?php

namespace App\Support;

/**
 * Ponto único de identificação de quem está executando ações internas.
 * TODO(sso): retornar o nome do usuário autenticado via SSO.
 */
class ResponsavelAtual
{
    public function nome(): string
    {
        return (string) config('ouvidoria.responsavel_padrao', 'Ouvidoria');
    }
}
