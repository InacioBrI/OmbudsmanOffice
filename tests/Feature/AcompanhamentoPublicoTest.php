<?php

namespace Tests\Feature;

use App\Models\Manifestacao;
use App\Models\Setor;
use App\Support\AcompanhamentoPublico;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class AcompanhamentoPublicoTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array<string, array{0: string, 1: array<int, string>, 2: string, 3: array<string, string>}>
     */
    public static function cenarios(): array
    {
        return [
            'recebida' => ['recebida', [], 'Solicitação recebida', [
                'recebida' => 'atual', 'analise' => 'pendente', 'resposta' => 'pendente', 'finalizada' => 'pendente',
            ]],
            'em análise sem encaminhamento' => ['em_analise', [], 'Estamos analisando sua solicitação', [
                'recebida' => 'concluida', 'analise' => 'atual', 'resposta' => 'pendente', 'finalizada' => 'pendente',
            ]],
            'encaminhada ao setor' => ['em_analise', ['pendente'], 'Estamos analisando sua solicitação junto ao setor responsável', [
                'recebida' => 'concluida', 'analise' => 'concluida', 'setor' => 'atual', 'retorno' => 'pendente', 'resposta' => 'pendente', 'finalizada' => 'pendente',
            ]],
            'aguardando retorno' => ['em_analise', ['em_atendimento'], 'Aguardando retorno do setor responsável', [
                'recebida' => 'concluida', 'analise' => 'concluida', 'setor' => 'concluida', 'retorno' => 'atual', 'resposta' => 'pendente', 'finalizada' => 'pendente',
            ]],
            'setor concluiu' => ['em_analise', ['concluido'], 'Estamos analisando sua solicitação', [
                'recebida' => 'concluida', 'analise' => 'concluida', 'setor' => 'concluida', 'retorno' => 'concluida', 'resposta' => 'pendente', 'finalizada' => 'pendente',
            ]],
            'encaminhamento cancelado é ignorado' => ['em_analise', ['cancelado'], 'Estamos analisando sua solicitação', [
                'recebida' => 'concluida', 'analise' => 'atual', 'resposta' => 'pendente', 'finalizada' => 'pendente',
            ]],
            'respondida' => ['respondida', [], 'Resposta enviada por e-mail', [
                'recebida' => 'concluida', 'analise' => 'concluida', 'resposta' => 'atual', 'finalizada' => 'pendente',
            ]],
            'concluída com setor' => ['concluida', ['concluido'], 'Solicitação finalizada', [
                'recebida' => 'concluida', 'analise' => 'concluida', 'setor' => 'concluida', 'retorno' => 'concluida', 'resposta' => 'concluida', 'finalizada' => 'concluida',
            ]],
            'arquivada direto' => ['arquivada', [], 'Solicitação finalizada', [
                'recebida' => 'concluida', 'finalizada' => 'concluida',
            ]],
        ];
    }

    /**
     * @param  array<int, string>  $encaminhamentos
     * @param  array<string, string>  $esperado
     */
    #[DataProvider('cenarios')]
    public function test_etapas_refletem_o_andamento(string $status, array $encaminhamentos, string $mensagem, array $esperado): void
    {
        $manifestacao = Manifestacao::create([
            'protocolo' => 'OUV-ETAPA-1',
            'tipo' => 'Reclamação',
            'descricao' => 'Teste',
            'status' => $status,
        ]);

        if ($encaminhamentos !== []) {
            $origem = Setor::factory()->ouvidoria()->create();
            foreach ($encaminhamentos as $statusEncaminhamento) {
                $manifestacao->encaminhamentos()->create([
                    'setor_origem_id' => $origem->id,
                    'setor_destino_id' => Setor::factory()->create()->id,
                    'responsavel' => 'Ouvidoria',
                    'motivo' => 'Motivo',
                    'status' => $statusEncaminhamento,
                ]);
            }
        }

        $resultado = app(AcompanhamentoPublico::class)->para($manifestacao);

        $this->assertSame($mensagem, $resultado['mensagem']);
        $this->assertSame($esperado, array_column($resultado['etapas'], 'estado', 'chave'));
    }

    public function test_tela_publica_exibe_as_etapas(): void
    {
        Manifestacao::create([
            'protocolo' => 'OUV-ETAPA-2',
            'tipo' => 'Reclamação',
            'descricao' => 'Teste',
            'status' => 'recebida',
        ]);

        $this->get(route('manifestacoes.acompanhar', ['protocolo' => 'OUV-ETAPA-2']))
            ->assertOk()
            ->assertSeeInOrder(['Situação atual', 'Solicitação recebida', 'Etapas do atendimento', 'Solicitação recebida', 'Estamos analisando sua solicitação', 'Resposta enviada por e-mail', 'Solicitação finalizada']);
    }
}
