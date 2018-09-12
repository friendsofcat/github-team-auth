<?php

namespace Tests;

use GrahamCampbell\GitHub\Facades\GitHub;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Friendsofcat\GitHubTeamAuth\Controllers\GithubTeamAuthOrgController;
use Friendsofcat\GitHubTeamAuth\Controllers\GithubTeamAuthSettingsController;
use Friendsofcat\GitHubTeamAuth\Organization;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;

class GithubTeamAuthOrgControllerTest extends TestCase
{
    use RefreshDatabase;

    public function testShouldAccessAddOrg()
    {
        $content = File::get(realpath(__DIR__.'/fixtures/org_array_response.json'));
        $user = $this->setUser('lord');
        Auth::shouldReceive('user')->andReturn($user);
        Cache::shouldReceive('get')->andReturn('token');
        GitHub::shouldReceive('me->organizations')->once()
            ->andReturn(json_decode($content, true));

        $gitHub_auth_service = \App::make(GithubTeamAuthOrgController::class);
        $data = $gitHub_auth_service->addOrg()->getData();
        $list_organization = $data['list_organization'];
        $content = json_decode($content, true);
        $this->assertEquals(count($list_organization), 3);
        $this->assertEquals($list_organization[0]['login'], $content[0]['login']);
    }

    public function testShouldNotFindToken()
    {
        $user = $this->setUser('lord');
        Auth::shouldReceive('user')->andReturn($user);
        Cache::shouldReceive('get')->andReturn(null);

        $gitHub_auth_service = \App::make(GithubTeamAuthOrgController::class);
        $data = $gitHub_auth_service->addOrg();
        $this->assertNotEmpty($data->getSession()->get('errors'));
    }


    public function testShouldStoreOrg()
    {
        $request = new Request ();
        $request->merge(['organization' => '1111:Friendsofcat1']);

        $gitHub_auth_service = \App::make(GithubTeamAuthOrgController::class);
        $gitHub_auth_service->storeOrg($request);
        $org = Organization::find('1111');
        $this->assertEquals($org->org_name, 'Friendsofcat1');
    }


    public function testShouldNotStoreOrgIfAlreadyExist()
    {
        factory(Organization::class)->create(['id' => '1955555', 'org_name' => 'FriendsofcatC']);

        $request = new Request ();
        $request->merge(['organization' => '1955555:FriendsofcatC']);

        $gitHub_auth_service = \App::make(GithubTeamAuthOrgController::class);
        $data = $gitHub_auth_service->storeOrg($request);
        $this->assertNotEmpty($data->getSession()->get('errors'));
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

    public function testShouldDeleteOrg()
    {
        $org = factory(Organization::class)->create(['id' => '1955555', 'org_name' => 'orgC']);
        factory(Organization::class)->create(['id' => '1955556', 'org_name' => 'orgD']);

        $gitHub_auth_service = \App::make(GithubTeamAuthOrgController::class);
        $gitHub_auth_service->deleteOrg($org->id);

        $gitHub_auth_service = \App::make(GithubTeamAuthSettingsController::class);
        $data = $gitHub_auth_service->getSettings()->getData();
        $organizations = $data['organizations'];
        $teams = $data['teams'];

        $this->assertEquals(count($teams), 0);
        $this->assertEquals(count($organizations), 1);
        $this->assertEquals($organizations[0]['org_name'], 'orgD');
    }

    public function testShouldNotDeleteOrg()
    {
        factory(Organization::class)->create(['id' => '1955555', 'org_name' => 'orgC']);
        factory(Organization::class)->create(['id' => '1955556', 'org_name' => 'orgD']);

        $gitHub_auth_service = \App::make(GithubTeamAuthOrgController::class);
        $gitHub_auth_service->deleteOrg(128903);

        $gitHub_auth_service = \App::make(GithubTeamAuthSettingsController::class);
        $data = $gitHub_auth_service->getSettings()->getData();
        $organizations = $data['organizations'];
        $teams = $data['teams'];

        $this->assertEquals(count($teams), 0);
        $this->assertEquals(count($organizations), 2);
        $this->assertEquals($organizations[0]['org_name'], 'orgC');
    }
}