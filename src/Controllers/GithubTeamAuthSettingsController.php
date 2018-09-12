<?php

namespace Friendsofcat\GitHubTeamAuth\Controllers;

use Illuminate\Routing\Controller;
use Friendsofcat\GitHubTeamAuth\Organization;
use Friendsofcat\GitHubTeamAuth\Team;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class GithubTeamAuthSettingsController extends Controller
{
    /**
     * @return \Illuminate\Contracts\View\View
     * @throws \Exception
     */
    public function getSettings()
    {
        try {
            $token = csrf_token();
            $organizations = Organization::get();
            $teams = Team::get();

            return view('github-team-auth::settings', compact('organizations', 'teams', 'token'));
        } catch (\Exception $e) {
            \Log::error("Error getting settings");
            \Log::error($e->getMessage());
            return redirect("/")->withMessage("Error visiting Settings page");
        }
    }
}
