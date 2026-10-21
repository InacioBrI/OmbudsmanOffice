<?php

namespace Database\Seeders;

use App\Models\Setor;
use Illuminate\Database\Seeder;

class SetorSeeder extends Seeder
{
    /**
     * Setores iniciais. Ajuste os e-mails diretamente no banco conforme as caixas reais.
     */
    public function run(): void
    {
        $setores = [
            ['nome' => 'Ouvidoria', 'email' => 'ouvidoria@exemplo.edu.br', 'is_ouvidoria' => true],
            ['nome' => 'Secretaria Acadêmica', 'email' => 'secretaria@exemplo.edu.br'],
            ['nome' => 'Financeiro', 'email' => 'financeiro@exemplo.edu.br'],
            ['nome' => 'Coordenação de Curso', 'email' => 'coordenacao@exemplo.edu.br'],
            ['nome' => 'TI', 'email' => 'ti@exemplo.edu.br'],
            ['nome' => 'Biblioteca', 'email' => 'biblioteca@exemplo.edu.br'],
        ];

        foreach ($setores as $setor) {
            Setor::updateOrCreate(['nome' => $setor['nome']], $setor);
        }
    }
}
