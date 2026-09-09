<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['protocolo', 'tipo', 'nome', 'rm', 'telefone', 'email', 'descricao', 'status', 'resposta', 'respondido_em', 'anexos'])]
class Manifestacao extends Model
{
    protected $table = 'manifestacoes';

    public const STATUSES = [
        'recebida' => 'Recebida',
        'em_analise' => 'Em análise',
        'concluida' => 'Concluída',
        'arquivada' => 'Arquivada',
    ];

    public const EM_ANDAMENTO = ['recebida', 'em_analise'];
    public const ENCERRADAS = ['concluida', 'arquivada'];

    public const TIPOS = ['Elogio', 'Reclamação', 'Denúncia', 'Solicitação'];

    protected function casts(): array
    {
        return [
            'anexos' => 'array',
            'respondido_em' => 'datetime',
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
}
