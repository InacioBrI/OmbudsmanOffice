<?php

namespace Database\Seeders;

use App\Models\Setor;
use Illuminate\Database\Seeder;

class SetorSeeder extends Seeder
{
    /**
     * Setores iniciais com a pessoa responsável (nome e e-mail). Ajuste aqui e rode
     * `php artisan db:seed --class=SetorSeeder` para atualizar.
     */
    public function run(): void
    {
        $setores = [
            ['nome' => 'Ouvidoria', 'responsavel_nome' => 'Responsável Ouvidoria', 'email' => 'ouvidoria@exemplo.edu.br', 'is_ouvidoria' => true],
            ['nome' => 'Secretaria Acadêmica', 'responsavel_nome' => 'Responsável Secretaria', 'email' => 'secretaria@exemplo.edu.br'],
            ['nome' => 'Financeiro', 'responsavel_nome' => 'Responsável Financeiro', 'email' => 'financeiro@exemplo.edu.br'],
            ['nome' => 'Coordenação de Curso', 'responsavel_nome' => 'Responsável Coordenação', 'email' => 'coordenacao@exemplo.edu.br'],
            ['nome' => 'TI', 'responsavel_nome' => 'Responsável TI', 'email' => 'ti@exemplo.edu.br'],
            ['nome' => 'Biblioteca', 'responsavel_nome' => 'Responsável Biblioteca', 'email' => 'biblioteca@exemplo.edu.br'],
        ];

        foreach ($setores as $setor) {
            Setor::updateOrCreate(['nome' => $setor['nome']], $setor);
        }
    }
}
