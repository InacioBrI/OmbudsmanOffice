<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('manifestacoes', function (Blueprint $table) {
            // Dados do manifestante
            $table->string('cpf')->nullable()->after('nome');
            $table->string('vinculo')->nullable()->after('cpf');
            $table->string('curso')->nullable()->after('vinculo');
            $table->string('unidade')->nullable()->after('curso');
            $table->string('semestre')->nullable()->after('unidade');

            // Dados da manifestação
            $table->string('area_envolvida')->nullable()->after('tipo');
            $table->string('assunto')->nullable()->after('area_envolvida');
            $table->date('data_ocorrido')->nullable()->after('descricao');
            $table->string('local_ocorrido')->nullable()->after('data_ocorrido');
            $table->text('pessoas_envolvidas')->nullable()->after('local_ocorrido');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('manifestacoes', function (Blueprint $table) {
            $table->dropColumn([
                'cpf',
                'vinculo',
                'curso',
                'unidade',
                'semestre',
                'area_envolvida',
                'assunto',
                'data_ocorrido',
                'local_ocorrido',
                'pessoas_envolvidas',
            ]);
        });
    }
};
