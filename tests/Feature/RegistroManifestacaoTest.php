<?php

namespace Tests\Feature;

use App\Models\Manifestacao;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class RegistroManifestacaoTest extends TestCase
{
    use RefreshDatabase;

    private const CPF_VALIDO = '529.982.247-25';

    private const CNPJ_VALIDO = '11.222.333/0001-81';

    /**
     * @return array<string, mixed>
     */
    private function dadosManifestacao(array $extra = []): array
    {
        return [
            'tipo' => 'Reclamação',
            'area_envolvida' => 'Secretaria',
            'assunto' => 'Atendimento',
            'data_ocorrido' => now()->subDay()->format('Y-m-d'),
            'local_ocorrido' => 'Bloco A',
            'possui_pessoas_envolvidas' => '0',
            'descricao' => 'Descrição detalhada da manifestação.',
            'ciente' => '1',
            ...$extra,
        ];
    }

    /**
     * @return array<string, array{0: string, 1: array<string, string>}>
     */
    public static function identificacaoPorVinculo(): array
    {
        $contato = ['telefone' => '(11)98765-4321', 'email' => 'pessoa@exemplo.com'];

        return [
            'aluno' => ['Aluno', ['nome' => 'Ana Aluna', 'rm' => '123456', 'cpf' => self::CPF_VALIDO, 'curso' => 'Direito', 'semestre' => '3º semestre', 'unidade' => 'Paraíso', ...$contato]],
            'ex-aluno' => ['Ex-aluno', ['nome' => 'Edu Ex', 'rm' => '654321', 'cpf' => self::CPF_VALIDO, 'curso' => 'Direito', 'semestre' => '10º semestre', 'unidade' => 'Vila Mariana', ...$contato]],
            'professor' => ['Professor', ['nome' => 'Paulo Prof', 'cpf' => self::CPF_VALIDO, 'ra' => '777', 'curso' => 'Medicina', ...$contato]],
            'colaborador' => ['Colaborador', ['nome' => 'Carla Colab', 'cpf' => self::CPF_VALIDO, 'ra' => '888', ...$contato]],
            'responsavel' => ['Responsável', ['nome' => 'Rita Resp', 'cpf' => self::CPF_VALIDO, 'aluno_nome' => 'Rafael Filho', 'curso' => 'Nutrição', 'semestre' => '1º semestre', ...$contato]],
            'comunidade externa' => ['Comunidade externa', ['nome' => 'Org Rep', 'cpf' => self::CNPJ_VALIDO, 'organizacao' => 'Associação X', ...$contato]],
        ];
    }

    public function test_formulario_e_exibido_com_vinculos_e_unidades(): void
    {
        $resposta = $this->get(route('manifestacoes.create'))->assertOk();

        foreach ([...Manifestacao::VINCULOS, ...Manifestacao::UNIDADES] as $opcao) {
            $resposta->assertSee($opcao);
        }
        $resposta->assertSee('Há pessoas envolvidas nesta manifestação?');
    }

    #[DataProvider('identificacaoPorVinculo')]
    public function test_registra_somente_campos_do_vinculo(string $vinculo, array $identificacao): void
    {
        $todosOsCampos = array_fill_keys(array_keys(Manifestacao::ROTULOS_CAMPOS), 'VALOR-EXTRA');

        $this->post(route('manifestacoes.store'), $this->dadosManifestacao([
            ...$todosOsCampos,
            ...$identificacao,
            'vinculo' => $vinculo,
        ]))->assertSessionHasNoErrors()->assertRedirect();

        $manifestacao = Manifestacao::sole();
        $this->assertSame($vinculo, $manifestacao->vinculo);

        foreach (array_keys(Manifestacao::ROTULOS_CAMPOS) as $campo) {
            $this->assertSame($identificacao[$campo] ?? null, $manifestacao->{$campo}, "Campo {$campo}");
        }
    }

    #[DataProvider('identificacaoPorVinculo')]
    public function test_campos_do_vinculo_sao_obrigatorios(string $vinculo): void
    {
        $this->post(route('manifestacoes.store'), $this->dadosManifestacao(['vinculo' => $vinculo]))
            ->assertSessionHasErrors(Manifestacao::CAMPOS_POR_VINCULO[$vinculo]);

        $this->assertDatabaseCount('manifestacoes', 0);
    }

    public function test_professor_nao_exige_rm_semestre_nem_unidade(): void
    {
        $this->post(route('manifestacoes.store'), $this->dadosManifestacao(['vinculo' => 'Professor']))
            ->assertSessionHasErrors(['ra'])
            ->assertSessionDoesntHaveErrors(['rm', 'semestre', 'unidade', 'aluno_nome', 'organizacao']);
    }

    public function test_cpf_invalido_e_rejeitado(): void
    {
        [, $dados] = self::identificacaoPorVinculo()['aluno'];

        $this->post(route('manifestacoes.store'), $this->dadosManifestacao([...$dados, 'vinculo' => 'Aluno', 'cpf' => '529.982.247-26']))
            ->assertSessionHasErrors('cpf');
    }

    public function test_cnpj_so_e_aceito_para_comunidade_externa(): void
    {
        [, $aluno] = self::identificacaoPorVinculo()['aluno'];
        $this->post(route('manifestacoes.store'), $this->dadosManifestacao([...$aluno, 'vinculo' => 'Aluno', 'cpf' => self::CNPJ_VALIDO]))
            ->assertSessionHasErrors('cpf');

        [, $externa] = self::identificacaoPorVinculo()['comunidade externa'];
        $this->post(route('manifestacoes.store'), $this->dadosManifestacao([...$externa, 'vinculo' => 'Comunidade externa', 'cpf' => '11.222.333/0001-82']))
            ->assertSessionHasErrors('cpf');
        $this->post(route('manifestacoes.store'), $this->dadosManifestacao([...$externa, 'vinculo' => 'Comunidade externa', 'cpf' => self::CPF_VALIDO]))
            ->assertSessionHasNoErrors();
    }

    public function test_telefone_e_unidade_invalidos_sao_rejeitados(): void
    {
        [, $dados] = self::identificacaoPorVinculo()['aluno'];

        $this->post(route('manifestacoes.store'), $this->dadosManifestacao([...$dados, 'vinculo' => 'Aluno', 'telefone' => '1234', 'unidade' => 'Outra']))
            ->assertSessionHasErrors(['telefone', 'unidade']);
    }

    public function test_pessoas_envolvidas_condicional(): void
    {
        [, $dados] = self::identificacaoPorVinculo()['professor'];
        $base = [...$dados, 'vinculo' => 'Professor'];

        $this->post(route('manifestacoes.store'), $this->dadosManifestacao([...$base, 'possui_pessoas_envolvidas' => null]))
            ->assertSessionHasErrors('possui_pessoas_envolvidas');

        $this->post(route('manifestacoes.store'), $this->dadosManifestacao([...$base, 'possui_pessoas_envolvidas' => '1']))
            ->assertSessionHasErrors('pessoas_envolvidas');

        $this->post(route('manifestacoes.store'), $this->dadosManifestacao([...$base, 'possui_pessoas_envolvidas' => '0', 'pessoas_envolvidas' => 'Ignorado']))
            ->assertSessionHasNoErrors();
        $semPessoas = Manifestacao::sole();
        $this->assertFalse($semPessoas->possui_pessoas_envolvidas);
        $this->assertNull($semPessoas->pessoas_envolvidas);

        $this->post(route('manifestacoes.store'), $this->dadosManifestacao([...$base, 'possui_pessoas_envolvidas' => '1', 'pessoas_envolvidas' => 'João, coordenador']))
            ->assertSessionHasNoErrors();
        $comPessoas = Manifestacao::latest('id')->first();
        $this->assertTrue($comPessoas->possui_pessoas_envolvidas);
        $this->assertSame('João, coordenador', $comPessoas->pessoas_envolvidas);
    }

    public function test_anexos_sao_salvos(): void
    {
        Storage::fake('public');
        [, $dados] = self::identificacaoPorVinculo()['colaborador'];

        $this->post(route('manifestacoes.store'), $this->dadosManifestacao([
            ...$dados,
            'vinculo' => 'Colaborador',
            'anexos' => [UploadedFile::fake()->create('prova.pdf', 100, 'application/pdf')],
        ]))->assertSessionHasNoErrors();

        $anexo = Manifestacao::sole()->anexos[0];
        $this->assertSame('prova.pdf', $anexo['nome']);
        Storage::disk('public')->assertExists($anexo['path']);
    }
}
