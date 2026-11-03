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
            ->assertSee('Estamos analisando sua solicitação junto ao setor responsável')
            ->assertDontSee('SETOR-INTERNO-X')
            ->assertDontSee('MOTIVO-INTERNO-SIGILOSO')
            ->assertDontSee('MENSAGEM-INTERNA-SIGILOSA')
            ->assertDontSee('Encaminh');
    }

    public function test_acompanhamento_publico_exibe_apenas_protocolo_data_e_status(): void
    {
        $manifestacao = Manifestacao::create([
            'protocolo' => 'OUV-PUB-2',
            'tipo' => 'TIPO-SIGILOSO',
            'descricao' => 'DESCRICAO-SIGILOSA',
            'status' => 'respondida',
            'resposta' => 'RESPOSTA-SIGILOSA',
            'respondido_em' => now(),
        ]);

        $this->get(route('manifestacoes.acompanhar', ['protocolo' => 'OUV-PUB-2']))
            ->assertOk()
            ->assertSee('OUV-PUB-2')
            ->assertSee('Resposta enviada por e-mail')
            ->assertSee($manifestacao->created_at->format('d/m/Y H:i'))
            ->assertDontSee('TIPO-SIGILOSO')
            ->assertDontSee('DESCRICAO-SIGILOSA')
            ->assertDontSee('RESPOSTA-SIGILOSA')
            ->assertDontSee('Resposta da Ouvidoria');
    }
}
