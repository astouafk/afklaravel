<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ModifyTokenLengthInBlacklistedTokensTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('blacklisted_tokens', function (Blueprint $table) {
            $table->text('token')->change(); // Changer le type en 'text'
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('blacklisted_tokens', function (Blueprint $table) {
            $table->string('token', 255)->change(); // Revenir au type 'string' de 255 caractères
        });
    }
}

