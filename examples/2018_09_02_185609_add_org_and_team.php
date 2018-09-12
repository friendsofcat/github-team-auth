<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddOrgAndTeam extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if(!$organization = \Friendsofcat\GitHubTeamAuth\Organization::find(1111111)){
            $organization = new \Friendsofcat\GitHubTeamAuth\Organization ();
            $organization->id = 11111111;
            $organization->org_name = 'friendOfCats';
            $organization->save();
        }

        if(!$team = \Friendsofcat\GitHubTeamAuth\Team::find(2222222)){
            $team = new \Friendsofcat\GitHubTeamAuth\Team ();
            $team->id = 222222;
            $team->team_name = 'cat-dog';
            $team->acl = 'ADMIN';
            $team->save();
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // skip, it is not necessary
    }

}