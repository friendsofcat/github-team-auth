<?php

namespace Friendsofcat\GitHubTeamAuth\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Friendsofcat\GitHubTeamAuth\GitHubAuthCore\GitHubAuthService;
use Friendsofcat\GitHubTeamAuth\Organization;
use GrahamCampbell\GitHub\Facades\GitHub;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class GithubTeamAuthOrgController extends Controller
{

    /**
     * @return \Illuminate\Contracts\View\View
     * @throws \Exception
     */
    public function addOrg()
    {

        try {
            $organization = new Organization();

            $list_organization = $this->getUserOrganization();

            return view('github-team-auth::add_org', compact('organization', 'list_organization'));
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
    public function storeOrg(Request $request)
    {
        try {
            $select_organization = $request->get('organization');
            $select_organization_array = explode(":", $select_organization);

            if (Organization::where('id', $select_organization_array[0])->count() > 0) {
                throw new \Exception("Organization already exist.");
            }

            $organization = new Organization();
            $organization->id = $select_organization_array[0];
            $organization->org_name = $select_organization_array[1];
            $organization->save();

            return redirect()->route('github-team-auth.index')->withMessage("Created Feature");
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            Log::error($e->getTraceAsString());

            return redirect()->route('github-team-auth.index')->withErrors($e->getMessage());
        }
    }

    /**
     * @param  string $org_id
     * @return \Illuminate\Contracts\View\View
     * @throws \Exception
     */
    public function deleteOrg($org_id)
    {
        try {
            $org = Organization::findOrFail($org_id);

            $org->delete();

            return redirect()->route(
                'github-team-auth.index'
            )->withMessage(sprintf("Organization deleted %d", $org_id));
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            Log::error($e->getTraceAsString());

            return redirect("/")->withMessage("Error while deleting organization");
        }
    }

    /**
     * @return array
     */
    protected function getUserOrganization()
    {
        $this->setUserToken();

        $list_organization = GitHub::me()->organizations();

        return $list_organization;
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
