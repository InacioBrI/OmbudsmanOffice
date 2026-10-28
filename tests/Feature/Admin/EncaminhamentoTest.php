<?php

namespace Tests\Feature\Admin;

use App\Models\Manifestacao;
use App\Models\ManifestacaoEncaminhamento;
use App\Models\Setor;
use App\Notifications\ManifestacaoEncaminhadaNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class EncaminhamentoTest extends TestCase
{
    use RefreshDatabase;

    private Setor $ouvidoria;

    private Setor $secretaria;

    protected function setUp(): void
    {
        parent::setUp();

        Notification::fake();
        $this->ouvidoria = Setor::factory()->ouvidoria()->create();
        $this->secretaria = Setor::factory()->create(['nome' => 'Secretaria Acadêmica']);
    }

    private function manifestacao(string $status = 'recebida', array $extra = []): Manifestacao
    {
        return Manifestacao::create(array_merge([
            'protocolo' => 'OUV-TESTE-'.uniqid(),
            'tipo' => 'Reclamação',
            'descricao' => 'Descrição detalhada da manifestação.',
            'status' => $status,
        ], $extra));
    }

    private function encaminhar(Manifestacao $manifestacao, array $dados = [])
    {
        return $this->post(route('admin.encaminhamentos.store', $manifestacao), array_merge([
            'setor_destino_id' => $this->secretaria->id,
            'motivo' => 'Verificar a situação de matrícula do aluno.',
        ], $dados));
    }

    public function test_encaminha_registra_e_notifica_o_setor(): void
    {
        $manifestacao = $this->manifestacao();

        $this->encaminhar($manifestacao)
            ->assertRedirect(route('admin.manifestacoes.show', $manifestacao))
            ->assertSessionHas('sucesso');

        $encaminhamento = ManifestacaoEncaminhamento::sole();
        $this->assertSame($this->ouvidoria->id, $encaminhamento->setor_origem_id);
        $this->assertSame($this->secretaria->id, $encaminhamento->setor_destino_id);
        $this->assertSame('Ouvidoria', $encaminhamento->responsavel);
        $this->assertSame('pendente', $encaminhamento->status);
        $this->assertFalse($encaminhamento->compartilhar_dados);
        $this->assertNull($encaminhamento->dados_compartilhados_em);

        Notification::assertSentTo($this->secretaria, ManifestacaoEncaminhadaNotification::class);
    }

    public function test_manifestacao_recebida_passa_para_em_analise(): void
    {
        $manifestacao = $this->manifestacao('recebida');

        $this->encaminhar($manifestacao);

        $this->assertSame('em_analise', $manifestacao->fresh()->status);
        $this->assertDatabaseHas('manifestacao_status_historicos', ['manifestacao_id' => $manifestacao->id, 'de' => 'recebida', 'para' => 'em_analise']);
    }

    public function test_manifestacao_em_analise_nao_muda_de_status(): void
    {
        $manifestacao = $this->manifestacao('em_analise');

        $this->encaminhar($manifestacao);

        $this->assertSame('em_analise', $manifestacao->fresh()->status);
        $this->assertDatabaseCount('manifestacao_status_historicos', 0);
    }

    public function test_motivo_e_setor_sao_obrigatorios(): void
    {
        $this->encaminhar($this->manifestacao(), ['setor_destino_id' => null, 'motivo' => ''])
            ->assertSessionHasErrorsIn('encaminhamento', ['setor_destino_id', 'motivo']);

        $this->assertDatabaseCount('manifestacao_encaminhamentos', 0);
        Notification::assertNothingSent();
    }

    public function test_nao_encaminha_para_ouvidoria_nem_setor_inativo(): void
    {
        $inativo = Setor::factory()->inativo()->create();

        foreach ([$this->ouvidoria, $inativo] as $setor) {
            $this->encaminhar($this->manifestacao(), ['setor_destino_id' => $setor->id])
                ->assertSessionHasErrorsIn('encaminhamento', ['setor_destino_id']);
        }

        $this->assertDatabaseCount('manifestacao_encaminhamentos', 0);
    }

    public function test_bloqueia_encaminhamento_ativo_duplicado(): void
    {
        $manifestacao = $this->manifestacao();

        $this->encaminhar($manifestacao);
        $this->encaminhar($manifestacao)->assertSessionHas('erro');

        $this->assertDatabaseCount('manifestacao_encaminhamentos', 1);
    }

    public function test_bloqueia_manifestacao_encerrada(): void
    {
        $this->encaminhar($this->manifestacao('concluida'))->assertSessionHas('erro');

        $this->assertDatabaseCount('manifestacao_encaminhamentos', 0);
    }

    public function test_compartilhar_dados_exige_confirmacao(): void
    {
        $this->encaminhar($this->manifestacao(), ['compartilhar_dados' => 1])
            ->assertSessionHasErrorsIn('encaminhamento', ['confirmacao_compartilhamento']);

        $this->assertDatabaseCount('manifestacao_encaminhamentos', 0);
    }

    public function test_compartilhar_dados_confirmado_registra_data(): void
    {
        $this->encaminhar($this->manifestacao(), ['compartilhar_dados' => 1, 'confirmacao_compartilhamento' => 1])
            ->assertSessionHas('sucesso');

        $encaminhamento = ManifestacaoEncaminhamento::sole();
        $this->assertTrue($encaminhamento->compartilhar_dados);
        $this->assertNotNull($encaminhamento->dados_compartilhados_em);
    }

    public function test_atualiza_status_do_encaminhamento_sem_alterar_manifestacao(): void
    {
        $manifestacao = $this->manifestacao('em_analise');
        $this->encaminhar($manifestacao);
        $encaminhamento = ManifestacaoEncaminhamento::sole();

        $this->patch(route('admin.encaminhamentos.status', $encaminhamento), ['status' => 'concluido'])->assertSessionHas('sucesso');
        $this->assertSame('concluido', $encaminhamento->fresh()->status);
        $this->assertSame('em_analise', $manifestacao->fresh()->status);

        $this->patch(route('admin.encaminhamentos.status', $encaminhamento), ['status' => 'pendente'])->assertSessionHas('erro');
    }

    public function test_encaminhamento_concluido_ou_cancelado_nao_pode_ser_reaberto(): void
    {
        $manifestacao = $this->manifestacao('em_analise');
        $this->encaminhar($manifestacao);
        $encaminhamento = ManifestacaoEncaminhamento::sole();

        foreach (['concluido', 'cancelado'] as $final) {
            $encaminhamento->update(['status' => $final]);

            $this->patch(route('admin.encaminhamentos.status', $encaminhamento), ['status' => 'em_atendimento'])->assertSessionHas('erro');
            $this->assertSame($final, $encaminhamento->fresh()->status);
        }

        $this->get(route('admin.manifestacoes.show', $manifestacao))->assertDontSee('Reabrir');
    }

    public function test_tela_de_detalhes_exibe_encaminhamento(): void
    {
        $manifestacao = $this->manifestacao();
        $this->encaminhar($manifestacao);

        $this->get(route('admin.manifestacoes.show', $manifestacao))
            ->assertOk()
            ->assertSee('Encaminhamentos internos')
            ->assertSee('Secretaria Acadêmica')
            ->assertSee('Verificar a situação de matrícula do aluno.');

        $this->get(route('admin.manifestacoes.index'))
            ->assertSee('Encaminhada a 1 setor');
    }

    public function test_modal_exibe_responsavel_do_departamento(): void
    {
        $this->secretaria->update(['responsavel_nome' => 'Joana Pereira', 'email' => 'joana@exemplo.edu.br']);

        $this->get(route('admin.manifestacoes.show', $this->manifestacao()))
            ->assertOk()
            ->assertSeeInOrder(['Departamento de destino', 'Responsável pelo setor', 'Motivo do encaminhamento', 'Enviar dados do aluno ao setor'])
            ->assertSee('Joana Pereira')
            ->assertSee('joana@exemplo.edu.br');
    }
}
