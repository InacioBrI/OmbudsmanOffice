<?php

namespace Tests\Feature\Admin;

use App\Models\Manifestacao;
use App\Models\ManifestacaoEncaminhamento;
use App\Models\Setor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class MensagemInternaTest extends TestCase
{
    use RefreshDatabase;

    private Manifestacao $manifestacao;

    private ManifestacaoEncaminhamento $encaminhamento;

    protected function setUp(): void
    {
        parent::setUp();

        $ouvidoria = Setor::factory()->ouvidoria()->create();
        $financeiro = Setor::factory()->create(['nome' => 'Financeiro']);

        $this->manifestacao = Manifestacao::create([
            'protocolo' => 'OUV-MSG-1',
            'tipo' => 'Reclamação',
            'descricao' => 'Teste de mensagens.',
            'status' => 'em_analise',
        ]);

        $this->encaminhamento = $this->manifestacao->encaminhamentos()->create([
            'setor_origem_id' => $ouvidoria->id,
            'setor_destino_id' => $financeiro->id,
            'responsavel' => 'Ouvidoria',
            'motivo' => 'Verificar boleto em aberto.',
            'status' => 'pendente',
        ]);

        Notification::fake();
    }

    private function enviar(string $remetente, string $corpo = 'Mensagem de teste')
    {
        return $this->post(route('admin.encaminhamentos.mensagens.store', $this->encaminhamento), [
            'remetente_tipo' => $remetente,
            'corpo' => $corpo,
        ]);
    }

    public function test_ouvidoria_registra_mensagem_local_sem_alterar_status(): void
    {
        $this->enviar('ouvidoria', 'Pode verificar, por favor?')->assertSessionHas('sucesso');

        $this->assertDatabaseHas('manifestacao_mensagens_internas', [
            'encaminhamento_id' => $this->encaminhamento->id,
            'remetente_tipo' => 'ouvidoria',
            'remetente_nome' => 'Ouvidoria',
            'origem' => 'local',
        ]);
        $this->assertSame('pendente', $this->encaminhamento->fresh()->status);
        $this->assertSame('em_analise', $this->manifestacao->fresh()->status);
        Notification::assertNothingSent();
    }

    public function test_resposta_do_setor_coloca_encaminhamento_em_atendimento(): void
    {
        $this->enviar('setor', 'Estamos verificando.')->assertSessionHas('sucesso');

        $this->assertDatabaseHas('manifestacao_mensagens_internas', ['remetente_tipo' => 'setor', 'remetente_nome' => 'Financeiro']);
        $this->assertSame('em_atendimento', $this->encaminhamento->fresh()->status);
    }

    public function test_valida_corpo_e_remetente(): void
    {
        $this->enviar('outro', '')->assertSessionHasErrorsIn('mensagem', ['corpo', 'remetente_tipo']);

        $this->assertDatabaseCount('manifestacao_mensagens_internas', 0);
    }

    public function test_nao_envia_mensagem_em_encaminhamento_cancelado(): void
    {
        $this->encaminhamento->update(['status' => 'cancelado']);

        $this->enviar('ouvidoria')->assertSessionHas('erro');

        $this->assertDatabaseCount('manifestacao_mensagens_internas', 0);
    }

    public function test_mensagens_aparecem_na_tela_de_detalhes(): void
    {
        $this->enviar('setor', 'Boleto regularizado ontem.');

        $this->get(route('admin.manifestacoes.show', $this->manifestacao))
            ->assertOk()
            ->assertSee('Comunicação interna')
            ->assertSee('Boleto regularizado ontem.');
    }
}
