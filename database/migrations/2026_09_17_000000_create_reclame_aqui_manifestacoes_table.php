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
        Schema::create('reclame_aqui_manifestacoes', function (Blueprint $table) {
            $table->id();

            // Identificador da reclamação no Reclame AQUI. Usado para deduplicar
            // e atualizar registros já sincronizados.
            // TODO(reclame-aqui): confirmar o nome/formato do identificador na doc oficial.
            $table->string('external_id')->unique();

            $table->string('protocolo')->nullable();
            $table->string('titulo')->nullable();
            $table->text('descricao')->nullable();
            $table->string('categoria')->nullable();

            // Dados do consumidor que registrou a reclamação.
            $table->string('consumidor_nome')->nullable();
            $table->string('consumidor_local')->nullable();

            // Status cru retornado pela RA API (ex.: "RESPONDIDA", "AVALIADA"...).
            $table->string('status_externo')->nullable();
            // Status interno mapeado (mesma convenção usada em manifestacoes).
            $table->string('status')->default('recebida');

            $table->text('resposta')->nullable();
            $table->timestamp('respondido_em')->nullable();

            $table->string('url')->nullable();
            $table->string('avaliacao')->nullable();
            $table->timestamp('publicado_em')->nullable();

            // Resposta bruta da API para auditoria/reprocessamento futuro.
            $table->json('payload')->nullable();

            // Momento da última sincronização bem-sucedida deste registro.
            $table->timestamp('sincronizado_em')->nullable();

            $table->timestamps();

            $table->index('status');
            $table->index('publicado_em');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reclame_aqui_manifestacoes');
    }
};
