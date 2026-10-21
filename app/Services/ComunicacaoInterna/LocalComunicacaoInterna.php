<?php

namespace App\Services\ComunicacaoInterna;

use App\Models\ManifestacaoEncaminhamento;
use App\Models\ManifestacaoMensagemInterna;
use App\Services\ComunicacaoInterna\Contracts\ComunicacaoInternaInterface;

/**
 * Implementação local: apenas grava a mensagem no banco, sem envio externo.
 */
class LocalComunicacaoInterna implements ComunicacaoInternaInterface
{
    public function enviar(ManifestacaoEncaminhamento $encaminhamento, string $remetenteTipo, string $remetenteNome, string $corpo): ManifestacaoMensagemInterna
    {
        return $encaminhamento->mensagens()->create([
            'remetente_tipo' => $remetenteTipo,
            'remetente_nome' => $remetenteNome,
            'corpo' => $corpo,
            'origem' => 'local',
        ]);
    }
}
