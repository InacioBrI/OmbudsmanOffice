<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['manifestacao_id', 'de', 'para', 'observacao', 'autor'])]
class ManifestacaoStatusHistorico extends Model
{
    protected $table = 'manifestacao_status_historicos';

    public function manifestacao(): BelongsTo
    {
        return $this->belongsTo(Manifestacao::class);
    }

    public function getDeLabelAttribute(): ?string
    {
        return $this->de ? (Manifestacao::STATUSES[$this->de] ?? $this->de) : null;
    }

    public function getParaLabelAttribute(): string
    {
        return Manifestacao::STATUSES[$this->para] ?? $this->para;
    }
}
