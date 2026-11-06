<?php

namespace Database\Factories;

use App\Models\Setor;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Setor>
 */
class SetorFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nome' => fake()->unique()->company(),
            'responsavel_nome' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'is_ouvidoria' => false,
            'ativo' => true,
        ];
    }

    public function ouvidoria(): static
    {
        return $this->state(fn () => ['nome' => 'Ouvidoria', 'is_ouvidoria' => true]);
    }

    public function inativo(): static
    {
        return $this->state(fn () => ['ativo' => false]);
    }
}
