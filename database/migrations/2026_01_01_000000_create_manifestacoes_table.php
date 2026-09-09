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
        Schema::create('manifestacoes', function (Blueprint $table) {
            $table->id();
            $table->string('protocolo')->unique();
            $table->string('tipo');
            $table->string('nome')->nullable();
            $table->string('rm')->nullable();
            $table->string('telefone')->nullable();
            $table->string('email')->nullable();
            $table->text('descricao');
            $table->string('status')->default('recebida');
            $table->text('resposta')->nullable();
            $table->timestamp('respondido_em')->nullable();
            $table->json('anexos')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('manifestacoes');
    }
};
