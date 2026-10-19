<?php

namespace Tests\Feature\Admin;

use App\Models\Manifestacao;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ManifestacoesIndexTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (['recebida' => 'PROT-RECEBIDA', 'em_analise' => 'PROT-ANALISE', 'concluida' => 'PROT-CONCLUIDA', 'arquivada' => 'PROT-ARQUIVADA'] as $status => $protocolo) {
            Manifestacao::create([
                'protocolo' => $protocolo,
                'tipo' => 'Reclamação',
                'descricao' => 'Teste',
                'status' => $status,
            ]);
        }
    }

    public function test_sem_filtro_lista_apenas_em_andamento(): void
    {
        $this->get(route('admin.manifestacoes.index'))
            ->assertOk()
            ->assertSee('PROT-RECEBIDA')
            ->assertSee('PROT-ANALISE')
            ->assertDontSee('PROT-CONCLUIDA')
            ->assertDontSee('PROT-ARQUIVADA');
    }

    public function test_filtro_todos_lista_todas(): void
    {
        $this->get(route('admin.manifestacoes.index', ['status' => 'todos']))
            ->assertOk()
            ->assertSee('PROT-RECEBIDA')
            ->assertSee('PROT-CONCLUIDA')
            ->assertSee('PROT-ARQUIVADA');
    }

    public function test_filtro_por_status_especifico(): void
    {
        $this->get(route('admin.manifestacoes.index', ['status' => 'concluida']))
            ->assertOk()
            ->assertSee('PROT-CONCLUIDA')
            ->assertDontSee('PROT-RECEBIDA')
            ->assertDontSee('PROT-ARQUIVADA');
    }

    public function test_rota_atendimentos_redireciona_preservando_filtros(): void
    {
        $this->get('/admin/atendimentos?busca=abc')
            ->assertStatus(301)
            ->assertRedirect(route('admin.manifestacoes.index', ['busca' => 'abc']));
    }
}
