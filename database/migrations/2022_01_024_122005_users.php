<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Users extends Migration{
    
    public function up(){
        Schema::table('users', function (Blueprint $table) {
            $table->text('token_id')->after('remember_token')->nullable();
        });
    }

    public function down(){
    }
}