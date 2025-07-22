<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterUserInteractionsNullableColumns extends Migration
{
    public function up()
    {
        Schema::table('user_interactions', function (Blueprint $table) {
            $table->string('cpf')->nullable()->change();
            $table->string('formaReceber')->nullable()->change();
            $table->string('unidadeEscolhida')->nullable()->change();
            $table->string('endereco')->nullable()->change();
            $table->string('cidade')->nullable()->change();
            $table->string('estado')->nullable()->change();
            $table->string('tipoEntrega')->nullable()->change();
            $table->string('cartao')->nullable()->change();
            $table->string('parcelamento')->nullable()->change();
            $table->string('numeroCartao')->nullable()->change();
            $table->string('quantidade')->nullable()->change();
            // coloque aqui outras colunas que deseja tornar opcionais
        });
    }

    public function down()
    {
        Schema::table('user_interactions', function (Blueprint $table) {
            $table->string('cpf')->nullable(false)->change();
            $table->string('formaReceber')->nullable(false)->change();
            $table->string('unidadeEscolhida')->nullable(false)->change();
            $table->string('endereco')->nullable(false)->change();
            $table->string('cidade')->nullable(false)->change();
            $table->string('estado')->nullable(false)->change();
            $table->string('tipoEntrega')->nullable(false)->change();
            $table->string('cartao')->nullable(false)->change();
            $table->string('parcelamento')->nullable(false)->change();
            $table->string('numeroCartao')->nullable(false)->change();
            $table->string('quantidade')->nullable(false)->change();
            // desfaz alterações no down()
        });
    }
}
