<?php

namespace Tests;

use GrahamCampbell\GitHub\Facades\GitHub;
use Illuminate\Support\Facades\App;
use Friendsofcat\GitHubTeamAuth\Events\GitHubUserEvent;
use Friendsofcat\GitHubTeamAuth\GitHubAuthCore\GitHubAuthService;
use Friendsofcat\GitHubTeamAuth\Organization;
use Friendsofcat\GitHubTeamAuth\Team;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;

class GitHubLoginTest extends TestCase
{
    
    use RefreshDatabase;

    public function testShouldDBOrgMatchUserAndCheckCache()
    {

        // We are expecting an fake event
        Event::fake();

        // Config
        $user = $this->setUser('lord');

        $db_org = factory(Organization::class)->create(['id' => '1955555', 'org_name' => 'OrgC']);
        $db_team1 = factory(Team::class)->create([ 'team_name' => 'team_nameA']);
        $db_team2 = factory(Team::class)->create([ 'team_name' => 'team_nameB']);

        // Mock Clients
        Socialite::shouldReceive('driver->stateless->user')->andReturn($user);

        $content = File::get(realpath(__DIR__.'/fixtures/org_array_response.json'));
        $team_content = File::get(realpath(__DIR__.'/fixtures/user_team_access.json'));

        GitHub::shouldReceive('me->organizations')->once()
            ->andReturn(json_decode($content, true));

        GitHub::shouldReceive('organization->teams->check')->twice()
            ->andReturn(json_decode($team_content, true));

        Cache::shouldReceive('forget')->once()->andReturnSelf();
        Cache::shouldReceive('forever')->once()->withArgs(['GITHUB-TOKEN-lord@gmail.com', 'token'])->andReturnSelf();

        // Call method with bussines
        $gitHub_auth_service = \App::make(GitHubAuthService::class);

        try{
            $gitHub_auth_service->handleProviderCallback();
        }catch (\Exception $e) {
            $this->assertEquals($e->getMessage(), 'It is necessary an authenticable user object for login');
        }

        $github_org = $gitHub_auth_service->getOrganization();
        $this->assertEquals($db_org->org_name, $github_org->org_name);

        $github_team_array = $gitHub_auth_service->getUserTeamArray();
        $this->assertEquals(count($github_team_array), 2);

        $this->assertEquals($github_team_array[0]->team_name, $db_team1->team_name);
        $this->assertEquals($github_team_array[1]->team_name, $db_team2->team_name);

        Event::assertDispatched(GitHubUserEvent::class, function ($e) use ($db_team1, $db_team2, $user) {
            return $e->user_team_array[0]->team_name === $db_team1->team_name &&
                    $e->user_team_array[1]->team_name === $db_team2->team_name &&
                    $user->name === $e->user_github_object->name;
        });
    }

    public function testShouldFailIfNoOrgInDB()
    {
        // Config
        $user = $this->setUser('lord');

        // Mock Clients
        Socialite::shouldReceive('driver->stateless->user')->andReturn($user);

        $content = File::get(realpath(__DIR__.'/fixtures/org_array_response.json'));

        $mocked_github_client = GitHub::shouldReceive('me->organizations')->once()
            ->andReturn(json_decode($content, true));

        // Call method with bussines
        App::instance(GitHub::class, $mocked_github_client);
        $gitHub_auth_service = \App::make(GitHubAuthService::class);

        try{
            $gitHub_auth_service->handleProviderCallback();
        }catch (\Exception $e) {
            $this->assertEquals($e->getMessage(), 'There isnt any organizations in DB');
        }
    }

    public function testShouldFailIfNoOrgInGitHub()
    {
        // Config
        $user = $this->setUser('lord');

        // Mock Clients
        Socialite::shouldReceive('driver->stateless->user')->andReturn($user);

        $mocked_github_client = GitHub::shouldReceive('me->organizations')->once()
            ->andReturn([]);

        // Call method with bussines
        App::instance(GitHub::class, $mocked_github_client);
        $gitHub_auth_service = \App::make(GitHubAuthService::class);

        try{
            $gitHub_auth_service->handleProviderCallback();
        }catch (\Exception $e) {
            $this->assertEquals($e->getMessage(), 'There isnt any organizations in GitHub');
        }
    }

    public function testShouldFailDBOrgNotMatch()
    {
        // Config
        $user = $this->setUser('lord');

        factory(Organization::class)->create(['id' => '1966666', 'org_name' => 'OrgD']);

        // Mock Clients
        Socialite::shouldReceive('driver->stateless->user')->andReturn($user);

        $content = File::get(realpath(__DIR__.'/fixtures/org_array_response.json'));

        $mocked_github_client = GitHub::shouldReceive('me->organizations')->once()
            ->andReturn(json_decode($content, true));

        // Call method with bussines
        App::instance(GitHub::class, $mocked_github_client);
        $gitHub_auth_service = \App::make(GitHubAuthService::class);


        try{
            $gitHub_auth_service->handleProviderCallback();
        }catch (\Exception $e) {
            $this->assertEquals($e->getMessage(), 'The user is not part of our org');
        }

    }

    public function testShouldFailDBTeamNotMatch()
    {
        // Config
        $user = $this->setUser('lord');

        $db_org = factory(Organization::class)->create(['id' => '1955555', 'org_name' => 'OrgD']);
        factory(Team::class)->create([ 'team_name' => 'team_nameA']);
        factory(Team::class)->create([ 'team_name' => 'team_nameB']);

        // Mock Clients
        Socialite::shouldReceive('driver->stateless->user')->andReturn($user);

        $content = File::get(realpath(__DIR__.'/fixtures/org_array_response.json'));
        $team_content = File::get(realpath(__DIR__.'/fixtures/user_without_access.json'));

        GitHub::shouldReceive('me->organizations')->once()
            ->andReturn(json_decode($content, true));

        GitHub::shouldReceive('organization->teams->check')->twice()
            ->andReturn(json_decode($team_content, true));

        // Call method with bussines
        $gitHub_auth_service = \App::make(GitHubAuthService::class);

        try{
            $gitHub_auth_service->handleProviderCallback();
        }catch (\Exception $e) {
            $this->assertEquals($e->getMessage(), 'The user is not part of our team or github user not active');
        }

    }

    public function testShouldDBOrgMatchUserTeamOnce()
    {
        // We are expecting an fake event
        Event::fake();

        $user = $this->setUser('lorda');

        $db_org = factory(Organization::class)->create(['id' => '1955555', 'org_name' => 'orgC']);
        factory(Team::class)->create([ 'team_name' => 'team_nameA']);
        $db_team2 = factory(Team::class)->create([ 'team_name' => 'team_nameB']);

        // Mock Clients
        Socialite::shouldReceive('driver->stateless->user')->andReturn($user);

        $content = File::get(realpath(__DIR__.'/fixtures/org_array_response.json'));
        $team_content_no_access = File::get(realpath(__DIR__.'/fixtures/user_without_access.json'));
        $team_content = File::get(realpath(__DIR__.'/fixtures/user_team_access.json'));

        GitHub::shouldReceive('me->organizations')->once()
            ->andReturn(json_decode($content, true));

        GitHub::shouldReceive('organization->teams->check')->once()
            ->andReturn(json_decode($team_content_no_access, true));

        GitHub::shouldReceive('organization->teams->check')->once()
            ->andReturn(json_decode($team_content, true));

        Cache::shouldReceive('forget')->once()->andReturnSelf();
        Cache::shouldReceive('forever')->once()->andReturnSelf();

        // Call method with bussines
        $gitHub_auth_service = \App::make(GitHubAuthService::class);

        try{
            $gitHub_auth_service->handleProviderCallback();
        }catch (\Exception $e) {
            $this->assertEquals($e->getMessage(), 'It is necessary an authenticable user object for login');
        }

        $github_org = $gitHub_auth_service->getOrganization();
        $this->assertEquals($db_org->org_name, $github_org->org_name);

        $github_team_array = $gitHub_auth_service->getUserTeamArray();
        $this->assertEquals(count($github_team_array), 1);

        $this->assertEquals($github_team_array[0]->team_name, $db_team2->team_name);

        Event::assertDispatched(GitHubUserEvent::class, function ($e) use ( $db_team2, $user) {
            return $e->user_team_array[0]->team_name === $db_team2->team_name &&
            $user->name === $e->user_github_object->name;
        });
    }

    public function testShouldFailIfnoTeamInDB()
    {
        $user = $this->setUser('lord');

        factory(Organization::class)->create(['id' => '1955555', 'org_name' => 'orgC']);

        // Mock Clients
        Socialite::shouldReceive('driver->stateless->user')->andReturn($user);

        $content = File::get(realpath(__DIR__.'/fixtures/org_array_response.json'));

        GitHub::shouldReceive('me->organizations')->once()
            ->andReturn(json_decode($content, true));

        // Call method with bussines
        $gitHub_auth_service = \App::make(GitHubAuthService::class);

        try{
            $gitHub_auth_service->handleProviderCallback();
        }catch (\Exception $e) {
            $this->assertEquals($e->getMessage(), 'There isnt any team in DB for org orgC');
        }
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