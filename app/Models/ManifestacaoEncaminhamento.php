<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'manifestacao_id',
    'setor_origem_id',
    'setor_destino_id',
    'responsavel',
    'motivo',
    'compartilhar_dados',
    'dados_compartilhados_em',
    'status',
    'email_enviado_em',
])]
class ManifestacaoEncaminhamento extends Model
{
    protected $table = 'manifestacao_encaminhamentos';

    public const STATUSES = [
        'pendente' => 'Pendente',
        'em_atendimento' => 'Em atendimento',
        'concluido' => 'Concluído pelo setor',
        'cancelado' => 'Cancelado',
    ];

    public const ATIVOS = ['pendente', 'em_atendimento'];

    /**
     * Transições permitidas para o status do encaminhamento (independente do status da manifestação).
     *
     * @var array<string, array<int, string>>
     */
    public const TRANSICOES = [
        'pendente' => ['em_atendimento', 'concluido', 'cancelado'],
        'em_atendimento' => ['concluido', 'cancelado'],
        'concluido' => [],
        'cancelado' => [],
    ];

    protected function casts(): array
    {
        return [
            'compartilhar_dados' => 'boolean',
            'dados_compartilhados_em' => 'datetime',
            'email_enviado_em' => 'datetime',
        ];
    }

    public function manifestacao(): BelongsTo
    {
        return $this->belongsTo(Manifestacao::class);
    }

    public function setorOrigem(): BelongsTo
    {
        return $this->belongsTo(Setor::class, 'setor_origem_id');
    }

    public function setorDestino(): BelongsTo
    {
        return $this->belongsTo(Setor::class, 'setor_destino_id');
    }

    public function mensagens(): HasMany
    {
        return $this->hasMany(ManifestacaoMensagemInterna::class, 'encaminhamento_id')->oldest();
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    public function isAtivo(): bool
    {
        return in_array($this->status, self::ATIVOS, true);
    }

    public function podeTransitarPara(string $status): bool
    {
        return in_array($status, self::TRANSICOES[$this->status] ?? [], true);
    }
}
