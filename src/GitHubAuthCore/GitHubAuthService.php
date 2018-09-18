<?php

namespace Friendsofcat\GitHubTeamAuth\GitHubAuthCore;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;
use Http\Adapter\Guzzle6\Client as GuzzleClient;
use GrahamCampbell\GitHub\Facades\GitHub;
use Mockery\CountValidator\Exception;
use Friendsofcat\GitHubTeamAuth\Organization;
use Friendsofcat\GitHubTeamAuth\Events\GitHubUserEvent;
use Friendsofcat\GitHubTeamAuth\Team;

class GitHubAuthService
{

    const CACHE_STRING_KEY = 'GITHUB-TOKEN-%s';

    /**
     * This the user data result from github Socialite
     *
     * @var \Laravel\Socialite\Two\User
     */
    protected $user_github_object;

    /**
     * Hold the organizations which this user has access
     *
     * @var array
     */
    protected $organization = null;


    /**
     * Hold the teams which this user is part
     *
     * @var array
     */
    protected $user_team_array = [];

    /**
     * Obtain the user information from GitHub.
     *
     * @return Response
     */
    public function handleProviderCallback()
    {
        $this->user_github_object = Socialite::driver('github')->stateless()->user();

        $this->setAuthenticateToken();

        $this->shouldUserHasAccess();

        $this->storeOrRefreshTokenInCache();

        $this->sendUserEvent();

        $this->loginIn();
    }

    /**
     * Authenticate user for access personal repo data
     */
    protected function setAuthenticateToken()
    {
        config(['github.connections.main.token' => $this->user_github_object->token]);
    }

    /**
     * Check if the user should have access to our app
     */
    protected function shouldUserHasAccess()
    {
        $this->validateIfUserIsPartDBOrg();

        $this->validateIfUserIsPartDBTeam();
    }

    /**
     * Validate if the github orgs whihc user has match with the ones we have in pur DB
     */
    private function validateIfUserIsPartDBOrg()
    {
        $user_github_organizations = $this->getUserOrganizations();

        $db_organizations= $this->getDbOrganizations();

        foreach ($db_organizations as $db_organization) {
            if (in_array($db_organization->id, array_column($user_github_organizations, 'id'))) {
                $this->organization = $db_organization;
            }
        }

        if (!$this->organization) {
            throw new \Exception(
                sprintf("The user is not part of our org")
            );
        }
    }

    /**
     * Obtain organizations which user have access in github
     */
    private function getUserOrganizations()
    {
        $organizations = GitHub::me()->organizations();

        if (count($organizations) <= 0) {
            throw new \Exception(
                sprintf("There isnt any organizations in GitHub")
            );
        }

        return $organizations;
    }

    /**
     * Obtain organizations which user should be part in db
     */
    private function getDbOrganizations()
    {
        $db_organizations= Organization::get();

        if (count($db_organizations) <= 0) {
            throw new \Exception(
                sprintf("There isnt any organizations in DB")
            );
        }

        return $db_organizations;
    }

    /**
     * Validate if the user has org teams  match with the ones we have in pur DB
     * @throws \Exception
     */
    private function validateIfUserIsPartDBTeam()
    {
        $teams = $this->dbTeamsExist();

        foreach ($teams as $team) {
            try {
                $git_reponse = GitHub::organization()->teams()->check(
                    $team->id,
                    $this->user_github_object->user['login']
                );
                $this->verifyPermissionResponse($git_reponse);

                array_push($this->user_team_array, $team);
            } catch (\Exception $e) {
                Log::debug($e->getMessage());
            }
        }

        if (!$this->getUserTeamArray()) {
            throw new \Exception(
                sprintf("The user is not part of our team or github user not active")
            );
        }
    }

    /**
     * verify if DB team exist for this org
     * @throws \Exception
     * @return array
     */
    private function dbTeamsExist()
    {
        $teams = Team::get();
        if (count($teams) <= 0) {
            throw new \Exception(
                sprintf("There isnt any team in DB for org %s", $this->organization->org_name)
            );
        }

        return $teams;
    }

    /**
     * @param $response_body
     * @throws \Exception
     */
    private function verifyPermissionResponse($response_body)
    {
        if (array_get($response_body, 'state', null) !== "active") {
            throw new \Exception("The user is not active in the git team");
        }
    }

    /**
     * Add this token in cache
     */
    private function storeOrRefreshTokenInCache()
    {
        $key = sprintf(GitHubAuthService::CACHE_STRING_KEY, $this->user_github_object->email);

        Cache::forget($key);

        Cache::forever($key, $this->user_github_object->token);
    }

    /**
     * Send event with user data and access grants groups
     */
    private function sendUserEvent()
    {
        event(new GitHubUserEvent($this->user_github_object, $this->user_team_array));
    }

    /**
     * Authenticate user before redirect him
     */
    protected function loginIn()
    {

        if (class_exists('\App\User')) {
            if ($user = \App\User::where('email', $this->user_github_object->email)->first()) {
                $user->email = $this->user_github_object->email;
            } else {
                $user = new \App\User();
                $user->email = $this->user_github_object->email;
            }
            Auth::login($user);
        } else {
            throw new \Exception(
                sprintf("It is necessary an authenticable user object for login")
            );
        }
    }


    /**
     * @return mixed
     */
    public function getOrganization()
    {
        return $this->organization;
    }

    /**
     * @return array
     */
    public function getUserTeamArray()
    {
        return $this->user_team_array;
    }
}
