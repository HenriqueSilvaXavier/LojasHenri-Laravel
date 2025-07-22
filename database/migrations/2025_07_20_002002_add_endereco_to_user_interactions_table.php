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
        Schema::table('user_interactions', function (Blueprint $table) {
            $table->string('cpf');
            $table->string('formaReceber');
            $table->string('endereco');
            $table->string('cidade');
            $table->string('estado');
            $table->string('tipoEntrega');
            $table->string('unidadeEscolhida');
            $table->string('cartao');
            $table->string('parcelamento');
            $table->string('numeroCartao');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_interactions', function (Blueprint $table) {
            $table->dropColumn([
                'cpf',
                'formaReceber',
                'endereco',
                'cidade',
                'estado',
                'tipoEntrega',
                'unidadeEscolhida',
                'cartao',
                'parcelamento',
                'numeroCartao',
            ]);
        });
    }
};
