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
        Schema::create('manifestacao_status_historicos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('manifestacao_id')->constrained('manifestacoes')->cascadeOnDelete();
            $table->string('de')->nullable();
            $table->string('para');
            $table->text('observacao')->nullable();
            $table->string('autor')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('manifestacao_status_historicos');
    }
};
