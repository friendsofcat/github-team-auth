<?php
/**
 * Created by PhpStorm.
 * User: luiz.albertoni
 * Date: 03/09/2018
 * Time: 15:40
 */

namespace Tests;

use GrahamCampbell\GitHub\Facades\GitHub;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Friendsofcat\GitHubTeamAuth\Controllers\GithubTeamAuthSettingsController;
use Friendsofcat\GitHubTeamAuth\Controllers\GithubTeamAuthTeamController;
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
use Illuminate\Http\Request;


class GithubTeamAuthTeamControllerTest extends TestCase
{
    use RefreshDatabase;


    public function testShouldAccessAddTeam()
    {
        $content = File::get(realpath(__DIR__.'/fixtures/team_array_response.json'));
        $user = $this->setUser('lord');
        Auth::shouldReceive('user')->andReturn($user);
        Cache::shouldReceive('get')->andReturn('token');
        GitHub::shouldReceive('me->teams')->once()
            ->andReturn(json_decode($content, true));

        $gitHub_auth_service = \App::make(GithubTeamAuthTeamController::class);
        $data = $gitHub_auth_service->addTeam()->getData();
        $list_teams = $data['list_teams'];
        $content = json_decode($content, true);
        $this->assertEquals(count($list_teams), 3);
        $this->assertEquals($list_teams[0]['name'], $content[0]['name']);
    }

    public function testShouldNotFindTokenForTeam()
    {
        $user = $this->setUser('lord');
        Auth::shouldReceive('user')->andReturn($user);
        Cache::shouldReceive('get')->andReturn(null);

        $gitHub_auth_service = \App::make(GithubTeamAuthTeamController::class);
        $data = $gitHub_auth_service->addTeam();
        $this->assertNotEmpty($data->getSession()->get('errors'));
    }


    public function testShouldStoreTeam()
    {
        $request = new Request ();
        $request->merge(['select_team' => '1111:Friendsofcat1', 'acl' => 'aclTest']);

        $gitHub_auth_service = \App::make(GithubTeamAuthTeamController::class);
        $gitHub_auth_service->storeTeam($request);
        $team = Team::find('1111');
        $this->assertEquals($team->team_name, 'Friendsofcat1');
        $this->assertEquals($team->acl, 'aclTest');
    }

    public function testShouldNotStoreOrgIfAlreadyExist()
    {
        factory(Team::class)->create(['id' => '1955555', 'team_name' => 'team_nameB']);

        $request = new Request ();
        $request->merge(['select_team' => '1955555:team_nameB']);

        $gitHub_auth_service = \App::make(GithubTeamAuthTeamController::class);
        $data = $gitHub_auth_service->storeTeam($request);
        $this->assertNotEmpty($data->getSession()->get('errors'));
    }


    public function testShouldDeleteTeam()
    {
        $team= factory(Team::class)->create([ 'team_name' => 'team_nameA']);
        factory(Team::class)->create([ 'team_name' => 'team_nameB']);

        $gitHub_auth_service = \App::make(GithubTeamAuthTeamController::class);
        $gitHub_auth_service->deleteTeam($team->id);
        $gitHub_auth_service = \App::make(GithubTeamAuthSettingsController::class);
        $data = $gitHub_auth_service->getSettings()->getData();
        $organizations = $data['organizations'];
        $teams = $data['teams'];

        $this->assertEquals(count($organizations), 0);
        $this->assertEquals(count($teams), 1);
        $this->assertEquals($teams[0]['team_name'], 'team_nameB');
    }

    public function testShouldNotDeleteTeam()
    {
        $team= factory(Team::class)->create([ 'team_name' => 'team_nameA']);
        factory(Team::class)->create([ 'team_name' => 'team_nameB']);

        $gitHub_auth_service = \App::make(GithubTeamAuthTeamController::class);
        $gitHub_auth_service->deleteTeam(129090);
        $gitHub_auth_service = \App::make(GithubTeamAuthSettingsController::class);
        $data = $gitHub_auth_service->getSettings()->getData();
        $organizations = $data['organizations'];
        $teams = $data['teams'];

        $this->assertEquals(count($organizations), 0);
        $this->assertEquals(count($teams), 2);
        $this->assertEquals($teams[0]['team_name'], 'team_nameA');
    }



    private function setUser($name)
    {
        $user_response = new \stdClass();
        $user_response->user['login'] = $name;
        $user_response->name = $name;
        $user_response->token = 'token';
        $user_response->email = $name.'@gmail.com';
        return $user_response;
    }
}