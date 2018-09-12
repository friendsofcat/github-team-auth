<?php

namespace Friendsofcat\GitHubTeamAuth\Controllers;

use GrahamCampbell\GitHub\Facades\GitHub;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Friendsofcat\GitHubTeamAuth\GitHubAuthCore\GitHubAuthService;
use Friendsofcat\GitHubTeamAuth\Organization;
use Friendsofcat\GitHubTeamAuth\Team;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Friendsofcat\GitHubTeamAuth\TeamGrants;

class GithubTeamAuthTeamController extends Controller
{

    /**
     * @return \Illuminate\Contracts\View\View
     * @throws \Exception
     */
    public function addTeam()
    {


        try {
            $team = new Team();

            $list_teams = $this->getUserTeams();

            $team_grants = TeamGrants::get();

            return view('github-team-auth::add_team', compact('team', 'list_teams', 'team_grants'));
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            Log::error($e->getTraceAsString());
            return redirect()->route('github-team-auth.index')->withErrors($e->getMessage());
        }
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Contracts\View\View
     * @throws \Exception
     */
    public function storeTeam(Request $request)
    {
        try {
            $select_team = $request->get('select_team');
            $acl = $request->get('acl');
            $select_team_array = explode(":", $select_team);

            if (Team::where('id', $select_team_array[0])->count() > 0) {
                throw new \Exception("Team already exist.");
            }
            
            $team = new Team();
            $team->id = $select_team_array[0];
            $team->team_name = $select_team_array[1];
            $team->acl = $acl;
            $team->save();


            return redirect()->route('github-team-auth.index')->withMessage("Team added");
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            Log::error($e->getTraceAsString());

            return redirect()->route('github-team-auth.index')->withErrors("Error while adding new team.");
        }
    }


    /**
     * @param  string $team_id
     * @return \Illuminate\Contracts\View\View
     * @throws \Exception
     */
    public function deleteTeam($team_id)
    {
        try {
            $team = Team::findOrFail($team_id);

            $team->delete();

            return redirect()->route(
                'github-team-auth.index'
            )->withMessage(sprintf("Team_id deleted %d", $team_id));
        } catch (\Exception $e) {
            \Log::error($e->getMessage());
            \Log::error($e->getTraceAsString());

            return redirect("/")->withMessage("Error while deleting team");
        }
    }

    /**
     * @return array
     */
    protected function getUserTeams()
    {
        $this->setUserToken();

        $list_teams = GitHub::me()->teams();

        return $list_teams;
    }


    /**
     * @throws \Exception
     */
    protected function setUserToken()
    {
        $key = sprintf(GitHubAuthService::CACHE_STRING_KEY, Auth::user()->email);

        $token = Cache::get($key, null);
        if (!$token) {
            throw new \Exception("The user is does not have a valid token, please login again in the system");
        }

        config(['github.connections.main.token' => $token]);
    }
}
