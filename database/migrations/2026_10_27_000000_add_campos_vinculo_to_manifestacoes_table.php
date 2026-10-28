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
            $table->string('ra')->nullable()->after('rm');
            $table->string('aluno_nome')->nullable()->after('ra');
            $table->string('organizacao')->nullable()->after('aluno_nome');
            $table->boolean('possui_pessoas_envolvidas')->nullable()->after('local_ocorrido');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('manifestacoes', function (Blueprint $table) {
            $table->dropColumn(['ra', 'aluno_nome', 'organizacao', 'possui_pessoas_envolvidas']);
        });
    }
};
