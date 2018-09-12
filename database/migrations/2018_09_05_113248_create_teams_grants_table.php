<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Friendsofcat\GitHubTeamAuth\TeamGrants;

class CreateTeamsGrantsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('team_grants', function (Blueprint $table) {
            $table->increments('id');
            $table->string('grant_name', 36);
            $table->timestamps();
        });

        $this->addDefaultValues();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('team_grants');
    }

    /**
     * Add fefault values
     *
     * @return void
     */
    protected function addDefaultValues()
    {
        $team_grants_admin = new TeamGrants();
        $team_grants_admin->grant_name = "ADMIN";
        $team_grants_admin->save();

        $team_grants_reader = new TeamGrants();
        $team_grants_reader->grant_name = "READER";
        $team_grants_reader->save();
    }
}

