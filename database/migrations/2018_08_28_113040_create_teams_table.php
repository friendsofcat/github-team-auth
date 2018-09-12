<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTeamsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
       $table_name = \Illuminate\Support\Facades\Config::get("githublogin.team_table_name");

        Schema::create($table_name, function (Blueprint $table) {
            $table->string('id')->unique();
            $table->string('team_name', 36);
            $table->string('acl');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $table_name = \Illuminate\Support\Facades\Config::get("githublogin.team_table_name");
        Schema::dropIfExists($table_name);
    }
}

