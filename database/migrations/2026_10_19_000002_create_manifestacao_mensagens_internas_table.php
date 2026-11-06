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
        Schema::create('manifestacao_mensagens_internas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('encaminhamento_id')->constrained('manifestacao_encaminhamentos')->cascadeOnDelete();
            $table->string('remetente_tipo');
            $table->string('remetente_nome');
            $table->text('corpo');
            // 'local' enquanto a integração com o Microsoft Graph não existir.
            $table->string('origem')->default('local');
            $table->string('external_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('manifestacao_mensagens_internas');
    }
};
