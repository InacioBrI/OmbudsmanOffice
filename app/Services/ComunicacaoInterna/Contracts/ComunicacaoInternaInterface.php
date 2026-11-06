<?php

namespace App\Services\ComunicacaoInterna\Contracts;

use App\Models\ManifestacaoEncaminhamento;
use App\Models\ManifestacaoMensagemInterna;

interface ComunicacaoInternaInterface
{
    /**
     * Registra (e, conforme o driver, transmite) uma mensagem da conversa interna do encaminhamento.
     */
    public function enviar(ManifestacaoEncaminhamento $encaminhamento, string $remetenteTipo, string $remetenteNome, string $corpo): ManifestacaoMensagemInterna;
}
