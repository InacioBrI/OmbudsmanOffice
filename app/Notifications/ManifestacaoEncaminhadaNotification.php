<?php

namespace App\Notifications;

use App\Models\Manifestacao;
use App\Models\ManifestacaoEncaminhamento;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Attachment;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Storage;

/**
 * Avisa o setor de destino sobre um novo encaminhamento. Destinado apenas a caixas internas:
 * nunca enviar ao solicitante. Não inclui a descrição completa nem CPF, telefone ou e-mail do aluno.
 */
class ManifestacaoEncaminhadaNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public ManifestacaoEncaminhamento $encaminhamento)
    {
        $this->afterCommit();
    }

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $encaminhamento = $this->encaminhamento->loadMissing('manifestacao', 'setorDestino');
        $manifestacao = $encaminhamento->manifestacao;

        $mensagem = (new MailMessage)
            ->subject("[Ouvidoria] Nova manifestação encaminhada – {$manifestacao->protocolo}")
            ->greeting('Olá, '.($encaminhamento->setorDestino->responsavel_nome ?: "equipe {$encaminhamento->setorDestino->nome}").'!')
            ->line('Uma nova manifestação foi direcionada à sua equipe pela Ouvidoria.')
            ->line("**Protocolo:** {$manifestacao->protocolo}")
            ->line("**Tipo:** {$manifestacao->tipo}")
            ->line('**Assunto:** '.($manifestacao->assunto ?: '—'))
            ->line('**Área envolvida:** '.($manifestacao->area_envolvida ?: '—'))
            ->line("**Registrada em:** {$manifestacao->created_at->format('d/m/Y H:i')}")
            ->line("**Encaminhada por:** {$encaminhamento->responsavel}")
            ->line("**Motivo do encaminhamento:** {$encaminhamento->motivo}");

        if ($encaminhamento->compartilhar_dados) {
            $mensagem->line('**Dados do solicitante (compartilhados pela Ouvidoria):**');

            foreach (Manifestacao::DADOS_COMPARTILHAVEIS as $campo => $rotulo) {
                if (filled($manifestacao->{$campo})) {
                    $mensagem->line("{$rotulo}: {$manifestacao->{$campo}}");
                }
            }

            $this->anexarArquivos($mensagem, $manifestacao);
        } else {
            $mensagem->line('Os dados pessoais do solicitante não foram compartilhados neste encaminhamento.');
        }

        return $mensagem
            ->action('Acessar manifestação', route('admin.manifestacoes.show', $manifestacao))
            ->line('Esta é uma comunicação interna e confidencial. Não a encaminhe ao solicitante.')
            ->salutation('Ouvidoria');
    }

    private function anexarArquivos(MailMessage $mensagem, Manifestacao $manifestacao): void
    {
        foreach ($manifestacao->anexos ?? [] as $anexo) {
            if (! empty($anexo['path']) && Storage::disk('public')->exists($anexo['path'])) {
                $mensagem->attach(
                    Attachment::fromStorageDisk('public', $anexo['path'])->as($anexo['nome'] ?? basename($anexo['path']))
                );
            }
        }
    }
}
