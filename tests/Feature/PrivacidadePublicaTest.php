<?php

namespace Tests\Feature;

use App\Models\Manifestacao;
use App\Models\Setor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class PrivacidadePublicaTest extends TestCase
{
    use RefreshDatabase;

    public function test_acompanhamento_publico_nao_exibe_informacoes_internas(): void
    {
        Notification::fake();
        Setor::factory()->ouvidoria()->create();
        $setor = Setor::factory()->create(['nome' => 'SETOR-INTERNO-X']);

        $manifestacao = Manifestacao::create([
            'protocolo' => 'OUV-PUB-1',
            'tipo' => 'Reclamação',
            'descricao' => 'Descrição pública da manifestação.',
            'status' => 'recebida',
        ]);

        $this->post(route('admin.encaminhamentos.store', $manifestacao), [
            'setor_destino_id' => $setor->id,
            'motivo' => 'MOTIVO-INTERNO-SIGILOSO',
        ]);
        $encaminhamento = $manifestacao->encaminhamentos()->sole();
        $this->post(route('admin.encaminhamentos.mensagens.store', $encaminhamento), [
            'remetente_tipo' => 'setor',
            'corpo' => 'MENSAGEM-INTERNA-SIGILOSA',
        ]);

        $this->get(route('manifestacoes.acompanhar', ['protocolo' => 'OUV-PUB-1']))
            ->assertOk()
            ->assertSee('Em análise')
            ->assertDontSee('SETOR-INTERNO-X')
            ->assertDontSee('MOTIVO-INTERNO-SIGILOSO')
            ->assertDontSee('MENSAGEM-INTERNA-SIGILOSA')
            ->assertDontSee('Encaminh');
    }
}
