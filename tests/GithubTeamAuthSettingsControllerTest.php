<?php

namespace Tests;

use GrahamCampbell\GitHub\Facades\GitHub;
use Illuminate\Support\Facades\App;
use Friendsofcat\GitHubTeamAuth\Controllers\GithubTeamAuthSettingsController;
use Friendsofcat\GitHubTeamAuth\Events\GitHubUserEvent;
use Friendsofcat\GitHubTeamAuth\GitHubAuthCore\GitHubAuthService;
use Friendsofcat\GitHubTeamAuth\Organization;
use Friendsofcat\GitHubTeamAuth\Team;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;
use Illuminate\Foundation\Testing\WithoutMiddleware;

class GithubTeamAuthSettingsControllerTest extends TestCase
{
    use RefreshDatabase;


    public function testShouldGetAllOrgTeamIndex()
    {
        factory(Organization::class)->create(['id' => '1955555', 'org_name' => 'orgC']);
        factory(Team::class)->create([ 'team_name' => 'team_nameA']);
        factory(Team::class)->create([ 'team_name' => 'team_nameB']);

        $gitHub_auth_service = \App::make(GithubTeamAuthSettingsController::class);
        $data = $gitHub_auth_service->getSettings()->getData();
        $organizations = $data['organizations'];
        $teams = $data['teams'];

        $this->assertEquals($organizations[0]['org_name'], 'orgC');
        $this->assertEquals(count($teams), 2);
        $this->assertEquals($teams[0]['team_name'], 'team_nameA');
        $this->assertEquals($teams[1]['team_name'], 'team_nameB');
    }
}