<?php

namespace Tests\Feature\Admin;

use App\Models\Manifestacao;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_exibe_indicadores_e_contagens_de_todos_os_status_e_tipos(): void
    {
        foreach (array_keys(Manifestacao::STATUSES) as $indice => $status) {
            $this->criarManifestacao("PROT-{$indice}", $status, Manifestacao::TIPOS[$indice % count(Manifestacao::TIPOS)]);
        }

        $response = $this->get(route('admin.dashboard'))
            ->assertOk()
            ->assertViewHas('total', 7)
            ->assertViewHas('emAndamento', 5)
            ->assertViewHas('encerradas', 2);

        $response->assertViewHas('porStatus', function ($contagens) {
            foreach (array_keys(Manifestacao::STATUSES) as $status) {
                if ($contagens[$status] !== 1) {
                    return false;
                }
            }

            return true;
        });

        $response->assertViewHas('porCategoria', function ($contagens) {
            foreach (Manifestacao::TIPOS as $tipo) {
                if (! isset($contagens[$tipo])) {
                    return false;
                }
            }

            return $contagens[Manifestacao::TIPOS[0]] === 2;
        });

        $response->assertSee('Resumo por status')->assertSee('Manifestações por categoria');
    }

    public function test_serie_mensal_inclui_meses_vazios_e_virada_do_ano_sem_mudar_totais_historicos(): void
    {
        $this->travelTo(Carbon::parse('2026-01-15 12:00:00'));

        $this->criarManifestacao('JUL', 'recebida', Manifestacao::TIPOS[0], '2025-07-10');
        $this->criarManifestacao('DEZ-ABERTA', 'em_analise', Manifestacao::TIPOS[0], '2025-12-05');
        $this->criarManifestacao('DEZ-FECHADA', 'concluida', Manifestacao::TIPOS[1], '2025-12-08');
        $this->criarManifestacao('JAN', 'arquivada', Manifestacao::TIPOS[1], '2026-01-03');
        $this->criarManifestacao('ANTIGA', 'recebida', Manifestacao::TIPOS[0], '2025-06-10');

        $this->get(route('admin.dashboard'))
            ->assertOk()
            ->assertViewHas('total', 5)
            ->assertViewHas('emAndamento', 3)
            ->assertViewHas('encerradas', 2)
            ->assertViewHas('serieMensal', fn ($serie) => $serie === [
                'labels' => ['07/2025', '08/2025', '09/2025', '10/2025', '11/2025', '12/2025', '01/2026'],
                'total' => [1, 0, 0, 0, 0, 2, 1],
                'emAndamento' => [1, 0, 0, 0, 0, 1, 0],
                'encerradas' => [0, 0, 0, 0, 0, 1, 1],
            ]);
    }

    public function test_dashboard_vazio_exibe_zeros_e_estados_vazios(): void
    {
        $this->get(route('admin.dashboard'))
            ->assertOk()
            ->assertViewHas('total', 0)
            ->assertViewHas('emAndamento', 0)
            ->assertViewHas('encerradas', 0)
            ->assertViewHas('serieMensal', fn ($serie) => $serie['total'] === array_fill(0, 7, 0))
            ->assertSee('Ainda não há dados para o gráfico.')
            ->assertDontSee('id="grafico-status"', false);
    }

    private function criarManifestacao(string $protocolo, string $status, string $tipo, ?string $data = null): void
    {
        $manifestacao = Manifestacao::create([
            'protocolo' => $protocolo,
            'status' => $status,
            'tipo' => $tipo,
            'descricao' => 'Teste',
        ]);

        if ($data) {
            $manifestacao->forceFill([
                'created_at' => $data,
                'updated_at' => $data,
            ])->save();
        }
    }
}
