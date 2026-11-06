<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['encaminhamento_id', 'remetente_tipo', 'remetente_nome', 'corpo', 'origem', 'external_id'])]
class ManifestacaoMensagemInterna extends Model
{
    protected $table = 'manifestacao_mensagens_internas';

    public const REMETENTES = ['ouvidoria', 'setor'];

    public function encaminhamento(): BelongsTo
    {
        return $this->belongsTo(ManifestacaoEncaminhamento::class, 'encaminhamento_id');
    }

    public function isDaOuvidoria(): bool
    {
        return $this->remetente_tipo === 'ouvidoria';
    }
}
