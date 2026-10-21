<?php

namespace App\Services\Encaminhamento;

use App\Exceptions\EncaminhamentoException;
use App\Models\Manifestacao;
use App\Models\ManifestacaoEncaminhamento;
use App\Models\ManifestacaoMensagemInterna;
use App\Models\Setor;
use App\Notifications\ManifestacaoEncaminhadaNotification;
use App\Services\ComunicacaoInterna\Contracts\ComunicacaoInternaInterface;
use App\Support\ResponsavelAtual;
use Illuminate\Support\Facades\DB;

/**
 * Encaminhamento interno de manifestações entre setores. O status do encaminhamento é
 * independente do status da manifestação, que só avança de "recebida" para "em_analise"
 * no primeiro encaminhamento.
 */
class EncaminhamentoService
{
    public function __construct(
        private ResponsavelAtual $responsavel,
        private ComunicacaoInternaInterface $comunicacao,
    ) {}

    public function encaminhar(Manifestacao $manifestacao, Setor $destino, string $motivo, bool $compartilharDados): ManifestacaoEncaminhamento
    {
        if ($manifestacao->isEncerrada()) {
            throw EncaminhamentoException::manifestacaoEncerrada();
        }

        $jaAtivo = $manifestacao->encaminhamentos()
            ->where('setor_destino_id', $destino->id)
            ->whereIn('status', ManifestacaoEncaminhamento::ATIVOS)
            ->exists();

        if ($jaAtivo) {
            throw EncaminhamentoException::duplicado($destino->nome);
        }

        return DB::transaction(function () use ($manifestacao, $destino, $motivo, $compartilharDados) {
            $responsavel = $this->responsavel->nome();

            if ($manifestacao->status === 'recebida') {
                $manifestacao->update(['status' => 'em_analise']);
                $manifestacao->registrarHistorico('recebida', 'em_analise', 'Manifestação em análise.', $responsavel);
            }

            $encaminhamento = $manifestacao->encaminhamentos()->create([
                'setor_origem_id' => Setor::ouvidoria()->id,
                'setor_destino_id' => $destino->id,
                'responsavel' => $responsavel,
                'motivo' => $motivo,
                'compartilhar_dados' => $compartilharDados,
                'dados_compartilhados_em' => $compartilharDados ? now() : null,
                'status' => 'pendente',
            ]);

            $destino->notify(new ManifestacaoEncaminhadaNotification($encaminhamento));

            return $encaminhamento;
        });
    }

    public function enviarMensagem(ManifestacaoEncaminhamento $encaminhamento, string $remetenteTipo, string $corpo): ManifestacaoMensagemInterna
    {
        if ($encaminhamento->status === 'cancelado') {
            throw EncaminhamentoException::encaminhamentoCancelado();
        }

        if ($encaminhamento->manifestacao->isEncerrada()) {
            throw EncaminhamentoException::manifestacaoEncerrada();
        }

        // TODO(sso/graph): identificar o remetente real em vez do nome do setor/responsável padrão.
        $remetenteNome = $remetenteTipo === 'setor'
            ? $encaminhamento->setorDestino->nome
            : $this->responsavel->nome();

        return DB::transaction(function () use ($encaminhamento, $remetenteTipo, $remetenteNome, $corpo) {
            $mensagem = $this->comunicacao->enviar($encaminhamento, $remetenteTipo, $remetenteNome, $corpo);

            if ($remetenteTipo === 'setor' && $encaminhamento->status === 'pendente') {
                $encaminhamento->update(['status' => 'em_atendimento']);
            }

            return $mensagem;
        });
    }

    public function atualizarStatus(ManifestacaoEncaminhamento $encaminhamento, string $status): ManifestacaoEncaminhamento
    {
        if ($encaminhamento->manifestacao->isEncerrada()) {
            throw EncaminhamentoException::manifestacaoEncerrada();
        }

        if (! $encaminhamento->podeTransitarPara($status)) {
            throw EncaminhamentoException::transicaoInvalida();
        }

        $encaminhamento->update(['status' => $status]);

        return $encaminhamento;
    }
}
