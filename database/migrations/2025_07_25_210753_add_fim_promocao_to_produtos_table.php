<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration{
    public function up(){
        Schema::table('produtos', function (Blueprint $table) {
            $table->dateTime('fim_promocao')->nullable()->after('promocao');
        });
    }

    public function down()
    {
        Schema::table('produtos', function (Blueprint $table) {
            $table->dropColumn('fim_promocao');
        });
    }

};
