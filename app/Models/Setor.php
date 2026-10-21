<?php

namespace App\Models;

use Database\Factories\SetorFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Notifications\Notifiable;

/**
 * @method static \Illuminate\Database\Eloquent\Builder ativos()
 * @method static \Illuminate\Database\Eloquent\Builder destinos()
 */
#[Fillable(['nome', 'email', 'is_ouvidoria', 'ativo'])]
class Setor extends Model
{
    /** @use HasFactory<SetorFactory> */
    use HasFactory, Notifiable;

    protected $table = 'setores';

    protected function casts(): array
    {
        return [
            'is_ouvidoria' => 'boolean',
            'ativo' => 'boolean',
        ];
    }

    public function encaminhamentosRecebidos(): HasMany
    {
        return $this->hasMany(ManifestacaoEncaminhamento::class, 'setor_destino_id');
    }

    public function scopeAtivos(Builder $query): Builder
    {
        return $query->where('ativo', true);
    }

    /**
     * Setores que podem receber encaminhamentos (ativos e diferentes da Ouvidoria).
     */
    public function scopeDestinos(Builder $query): Builder
    {
        return $query->ativos()->where('is_ouvidoria', false);
    }

    public static function ouvidoria(): self
    {
        return static::where('is_ouvidoria', true)->firstOrFail();
    }

    public function routeNotificationForMail(): string
    {
        return $this->email;
    }
}
