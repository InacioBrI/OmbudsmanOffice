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
        Schema::create('manifestacao_encaminhamentos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('manifestacao_id')->constrained('manifestacoes')->cascadeOnDelete();
            $table->foreignId('setor_origem_id')->constrained('setores');
            $table->foreignId('setor_destino_id')->constrained('setores');
            $table->string('responsavel');
            $table->text('motivo');
            $table->boolean('compartilhar_dados')->default(false);
            $table->timestamp('dados_compartilhados_em')->nullable();
            $table->string('status')->default('pendente');
            $table->timestamp('email_enviado_em')->nullable();
            $table->timestamps();

            $table->index(['manifestacao_id', 'setor_destino_id'], 'encaminhamentos_manifestacao_destino_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('manifestacao_encaminhamentos');
    }
};
