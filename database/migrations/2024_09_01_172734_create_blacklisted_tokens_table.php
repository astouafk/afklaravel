<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBlacklistedTokensTable extends Migration
{
    public function up()
    {
        Schema::create('blacklisted_tokens', function (Blueprint $table) {
            $table->id();
            $table->string('token')->unique();  
            $table->enum('type', ['access', 'refresh']);  
            $table->timestamp('revoked_at')->nullable();;  
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('blacklisted_tokens');
    }
}

