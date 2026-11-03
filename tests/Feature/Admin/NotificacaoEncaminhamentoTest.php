<?php

namespace Tests\Feature\Admin;

use App\Models\Manifestacao;
use App\Models\ManifestacaoEncaminhamento;
use App\Models\Setor;
use App\Notifications\ManifestacaoEncaminhadaNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class NotificacaoEncaminhamentoTest extends TestCase
{
    use RefreshDatabase;

    private Manifestacao $manifestacao;

    private Setor $secretaria;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
        Storage::disk('public')->put('anexos/comprovante.pdf', UploadedFile::fake()->create('comprovante.pdf', 10)->getContent());

        Setor::factory()->ouvidoria()->create();
        $this->secretaria = Setor::factory()->create(['nome' => 'Secretaria Acadêmica']);

        $this->manifestacao = Manifestacao::create([
            'protocolo' => 'OUV-20261019-ABC123',
            'tipo' => 'Reclamação',
            'assunto' => 'Problema na matrícula',
            'area_envolvida' => 'Secretaria',
            'descricao' => 'DESCRICAO-SIGILOSA do aluno sobre o ocorrido.',
            'status' => 'em_analise',
            'nome' => 'Maria da Silva',
            'cpf' => '123.456.789-00',
            'rm' => 'RM98765',
            'telefone' => '(11) 99999-0000',
            'email' => 'maria@aluno.com',
            'curso' => 'Enfermagem',
            'anexos' => [['path' => 'anexos/comprovante.pdf', 'nome' => 'comprovante.pdf', 'tamanho' => 10]],
        ]);
    }

    private function encaminhar(bool $compartilhar): void
    {
        $this->post(route('admin.encaminhamentos.store', $this->manifestacao), [
            'setor_destino_id' => $this->secretaria->id,
            'motivo' => 'Verificar a situação de matrícula.',
            'compartilhar_dados' => $compartilhar ? 1 : 0,
            'confirmacao_compartilhamento' => $compartilhar ? 1 : 0,
        ])->assertSessionHas('sucesso');
    }

    private function renderizar(): array
    {
        $encaminhamento = ManifestacaoEncaminhamento::sole();
        $mail = (new ManifestacaoEncaminhadaNotification($encaminhamento))->toMail($this->secretaria);

        return [(string) $mail->render(), $mail];
    }

    public function test_email_sem_compartilhamento_envia_apenas_informacoes_basicas(): void
    {
        $this->encaminhar(false);
        [$html, $mail] = $this->renderizar();

        $this->assertStringContainsString('OUV-20261019-ABC123', $html);
        $this->assertStringContainsString('Problema na matrícula', $html);
        $this->assertStringContainsString('Verificar a situação de matrícula.', $html);
        $this->assertStringContainsString(route('admin.manifestacoes.show', $this->manifestacao), $html);

        foreach (['Maria da Silva', 'RM98765', '123.456.789-00', '99999-0000', 'maria@aluno.com', 'DESCRICAO-SIGILOSA'] as $proibido) {
            $this->assertStringNotContainsString($proibido, $html);
        }
        $this->assertEmpty($mail->rawAttachments);
        $this->assertEmpty($mail->attachments);
    }

    public function test_email_com_compartilhamento_inclui_dados_permitidos_e_anexos(): void
    {
        $this->encaminhar(true);
        [$html, $mail] = $this->renderizar();

        $this->assertStringContainsString('Maria da Silva', $html);
        $this->assertStringContainsString('RM98765', $html);
        $this->assertStringContainsString('Enfermagem', $html);

        foreach (['123.456.789-00', '99999-0000', 'maria@aluno.com', 'DESCRICAO-SIGILOSA'] as $proibido) {
            $this->assertStringNotContainsString($proibido, $html);
        }
        $this->assertCount(1, $mail->rawAttachments);
        $this->assertSame('comprovante.pdf', $mail->rawAttachments[0]['name']);
    }

    public function test_email_saudacao_usa_nome_do_responsavel(): void
    {
        $this->secretaria->update(['responsavel_nome' => 'Joana Pereira']);
        $this->encaminhar(false);
        [$html] = $this->renderizar();

        $this->assertStringContainsString('Olá, Joana Pereira!', $html);
    }

    public function test_envio_registra_data_do_email(): void
    {
        $this->encaminhar(false);

        $this->assertNotNull(ManifestacaoEncaminhamento::sole()->email_enviado_em);
    }
}
