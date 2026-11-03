<?php

namespace App\Support;

use App\Models\Manifestacao;
use App\Models\ManifestacaoEncaminhamento;
use App\Models\ManifestacaoStatusHistorico;

/**
 * Traduz o andamento interno de uma manifestação em etapas públicas para o cidadão.
 * Consulta os encaminhamentos apenas de forma agregada (status), sem expor setores,
 * motivos ou mensagens internas.
 */
class AcompanhamentoPublico
{
    public const ETAPAS = [
        'recebida' => [
            'titulo' => 'Solicitação recebida',
            'descricao' => 'Sua manifestação foi registrada com sucesso.',
        ],
        'analise' => [
            'titulo' => 'Estamos analisando sua solicitação',
            'descricao' => 'A Ouvidoria está verificando as informações enviadas.',
        ],
        'setor' => [
            'titulo' => 'Estamos analisando sua solicitação junto ao setor responsável',
            'descricao' => 'Sua manifestação está sendo tratada em conjunto com a área competente.',
        ],
        'retorno' => [
            'titulo' => 'Aguardando retorno do setor responsável',
            'descricao' => 'Estamos aguardando as informações necessárias para responder você.',
        ],
        'resposta' => [
            'titulo' => 'Resposta enviada por e-mail',
            'descricao' => 'Confira a resposta no e-mail informado no registro.',
        ],
        'finalizada' => [
            'titulo' => 'Solicitação finalizada',
            'descricao' => 'O atendimento da sua manifestação foi encerrado.',
        ],
    ];

    /**
     * @return array{mensagem: string, atual: ?string, etapas: array<int, array{chave: string, titulo: string, descricao: string, estado: string}>}
     */
    public function para(Manifestacao $manifestacao): array
    {
        $statusEncaminhamentos = ManifestacaoEncaminhamento::query()
            ->where('manifestacao_id', $manifestacao->id)
            ->where('status', '!=', 'cancelado')
            ->pluck('status');

        $teveEncaminhamento = $statusEncaminhamentos->isNotEmpty();
        $comSetor = $teveEncaminhamento ? ['setor', 'retorno'] : [];

        [$concluidas, $atual] = match ($manifestacao->status) {
            'recebida' => [[], 'recebida'],
            'em_analise' => match (true) {
                $statusEncaminhamentos->contains('pendente') => [['recebida', 'analise'], 'setor'],
                $statusEncaminhamentos->contains('em_atendimento') => [['recebida', 'analise', 'setor'], 'retorno'],
                $teveEncaminhamento => [['recebida', 'analise', 'setor', 'retorno'], null],
                default => [['recebida'], 'analise'],
            },
            'respondida' => [['recebida', 'analise', ...$comSetor], 'resposta'],
            'concluida' => [['recebida', 'analise', ...$comSetor, 'resposta', 'finalizada'], 'finalizada'],
            default => [$this->ocorridasAteArquivar($manifestacao, $comSetor), 'finalizada'],
        };

        $visiveis = $manifestacao->isEncerrada()
            ? array_keys(array_intersect_key(self::ETAPAS, array_flip($concluidas)))
            : ['recebida', 'analise', ...$comSetor, 'resposta', 'finalizada'];

        $etapas = array_map(fn (string $chave) => [
            'chave' => $chave,
            ...self::ETAPAS[$chave],
            'estado' => match (true) {
                in_array($chave, $concluidas, true) => 'concluida',
                $chave === $atual => 'atual',
                default => 'pendente',
            },
        ], $visiveis);

        return [
            'mensagem' => self::ETAPAS[$atual ?? 'analise']['titulo'],
            'atual' => $atual,
            'etapas' => $etapas,
        ];
    }

    /**
     * @param  array<int, string>  $comSetor
     * @return array<int, string>
     */
    private function ocorridasAteArquivar(Manifestacao $manifestacao, array $comSetor): array
    {
        $analisada = $comSetor !== [] || ManifestacaoStatusHistorico::query()
            ->where('manifestacao_id', $manifestacao->id)
            ->where('para', 'em_analise')
            ->exists();

        return array_values(array_filter([
            'recebida',
            $analisada ? 'analise' : null,
            ...$comSetor,
            $manifestacao->respondido_em ? 'resposta' : null,
            'finalizada',
        ]));
    }
}
