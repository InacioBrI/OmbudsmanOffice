<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @method static \Illuminate\Database\Eloquent\Builder emAndamento()
 * @method static \Illuminate\Database\Eloquent\Builder encerradas()
 */
#[Fillable([
    'protocolo',
    'tipo',
    'area_envolvida',
    'assunto',
    'nome',
    'cpf',
    'vinculo',
    'curso',
    'unidade',
    'semestre',
    'rm',
    'telefone',
    'email',
    'descricao',
    'data_ocorrido',
    'local_ocorrido',
    'pessoas_envolvidas',
    'status',
    'resposta',
    'respondido_em',
    'anexos',
])]
class Manifestacao extends Model
{
    protected $table = 'manifestacoes';

    public const STATUSES = [
        'recebida' => 'Recebida',
        'em_analise' => 'Em análise',
        'respondida' => 'Respondida',
        'concluida' => 'Concluída',
        'arquivada' => 'Arquivada',
    ];

    public const EM_ANDAMENTO = ['recebida', 'em_analise', 'respondida'];

    public const ENCERRADAS = ['concluida', 'arquivada'];

    /**
     * Transições de status permitidas a partir de cada status (fluxo sequencial).
     *
     * @var array<string, array<int, string>>
     */
    public const TRANSICOES = [
        'recebida' => ['em_analise', 'arquivada'],
        'em_analise' => ['respondida', 'arquivada'],
        'respondida' => ['concluida', 'arquivada'],
        'concluida' => ['arquivada'],
        'arquivada' => [],
    ];

    public const TIPOS = ['Reclamação', 'Sugestão', 'Denúncia', 'Elogio', 'Solicitação', 'Informação'];

    public const VINCULOS = ['Aluno', 'Ex-aluno', 'Professor', 'Colaborador', 'Responsável', 'Comunidade externa'];

    /**
     * Dados do solicitante que podem ser compartilhados com um setor no encaminhamento
     * (CPF, telefone e e-mail nunca são compartilhados).
     *
     * @var array<string, string>
     */
    public const DADOS_COMPARTILHAVEIS = [
        'nome' => 'Nome',
        'rm' => 'RM',
        'vinculo' => 'Vínculo',
        'curso' => 'Curso',
        'unidade' => 'Unidade',
        'semestre' => 'Semestre',
    ];

    protected function casts(): array
    {
        return [
            'anexos' => 'array',
            'data_ocorrido' => 'date',
            'respondido_em' => 'datetime',
        ];
    }

    public function historicos(): HasMany
    {
        return $this->hasMany(ManifestacaoStatusHistorico::class)->latest();
    }

    /**
     * Encaminhamentos internos entre setores. Uso exclusivo do painel administrativo:
     * nunca deve ser carregado nas telas públicas.
     */
    public function encaminhamentos(): HasMany
    {
        return $this->hasMany(ManifestacaoEncaminhamento::class)->latest();
    }

    public function isEncerrada(): bool
    {
        return in_array($this->status, self::ENCERRADAS, true);
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
     * Status para os quais a manifestação pode transitar, incluindo o status atual.
     *
     * @return array<int, string>
     */
    public function statusPermitidos(): array
    {
        return array_values(array_unique(array_merge(
            [$this->status],
            self::TRANSICOES[$this->status] ?? []
        )));
    }

    public function registrarHistorico(?string $de, string $para, ?string $observacao = null, ?string $autor = null): ManifestacaoStatusHistorico
    {
        return $this->historicos()->create([
            'de' => $de,
            'para' => $para,
            'observacao' => $observacao,
            'autor' => $autor,
        ]);
    }
}
