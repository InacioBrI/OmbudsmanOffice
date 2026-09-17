<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * @method static \Illuminate\Database\Eloquent\Builder emAndamento()
 * @method static \Illuminate\Database\Eloquent\Builder encerradas()
 */
#[Fillable([
    'external_id',
    'protocolo',
    'titulo',
    'descricao',
    'categoria',
    'consumidor_nome',
    'consumidor_local',
    'status_externo',
    'status',
    'resposta',
    'respondido_em',
    'url',
    'avaliacao',
    'publicado_em',
    'payload',
    'sincronizado_em',
])]
class ReclameAquiManifestacao extends Model
{
    protected $table = 'reclame_aqui_manifestacoes';

    /**
     * Status internos, mantidos em sintonia com App\Models\Manifestacao para
     * reaproveitar badges e telas do painel.
     */
    public const STATUSES = [
        'recebida' => 'Recebida',
        'em_analise' => 'Em análise',
        'encaminhada' => 'Encaminhada',
        'aguardando_resposta' => 'Aguardando resposta da área',
        'respondida' => 'Respondida',
        'concluida' => 'Concluída',
        'arquivada' => 'Arquivada',
    ];

    public const EM_ANDAMENTO = ['recebida', 'em_analise', 'encaminhada', 'aguardando_resposta', 'respondida'];

    public const ENCERRADAS = ['concluida', 'arquivada'];

    /**
     * Mapa de status cru da RA API para o status interno.
     *
     * TODO(reclame-aqui): completar com os valores reais retornados pela API
     * (ex.: "PENDING", "ANSWERED", "EVALUATED"...) assim que tivermos a doc.
     *
     * @var array<string, string>
     */
    public const MAPA_STATUS_EXTERNO = [
        // 'NOVA' => 'recebida',
        // 'RESPONDIDA' => 'respondida',
        // 'AVALIADA' => 'concluida',
    ];

    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'respondido_em' => 'datetime',
            'publicado_em' => 'datetime',
            'sincronizado_em' => 'datetime',
        ];
    }

    public function scopeEmAndamento(Builder $query): Builder
    {
        return $query->whereIn('status', self::EM_ANDAMENTO);
    }

    public function scopeEncerradas(Builder $query): Builder
    {
        return $query->whereIn('status', self::ENCERRADAS);
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    /**
     * Converte um status cru da RA API para o status interno correspondente.
     * Enquanto o mapa não estiver preenchido, mantém "recebida" como padrão.
     */
    public static function mapearStatus(?string $statusExterno): string
    {
        if ($statusExterno === null) {
            return 'recebida';
        }

        return self::MAPA_STATUS_EXTERNO[$statusExterno] ?? 'recebida';
    }
}
