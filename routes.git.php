<?php

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Config;

Route::group(['middleware' => ['web']], function () {

    /**
     * Immediate Post to authenticate
     */
    Route::get('auth/github', [
        'as' => 'git_auth',
        'uses' => function () {

            try {
                return Socialite::driver('github')->scopes(['read:user', 'read:org'])->redirect();
            } catch (\Exception $e) {
                $message = sprintf(
                    "Error authenticating %s line %s",
                    $e->getMessage(),
                    $e->getLine()
                );
                Log::debug($message);

                return Redirect::to('login')->withErrors(
                    [
                        "Error authenticating."
                    ]
                );
            }
        }
    ]);

    Route::get('auth/github/callback', [
        'as' => 'git_authorize',
        'uses' => function () {

            try {
                /** @var GitHubAuthService */
                $git_hub_auth_service = App::make(\Friendsofcat\GitHubTeamAuth\GitHubAuthCore\GitHubAuthService::class);
                $git_hub_auth_service->handleProviderCallback();
                 
                $custom_redirect =  Config::get('githublogin.custom_redirect');

                return redirect()->intended($custom_redirect);
            } catch (\Exception $e) {
                $message = sprintf(
                    "Error authenticating %s line %s",
                    $e->getMessage(),
                    $e->getLine()
                );
                Log::debug($message);

                return Redirect::to('login')->withErrors([
                    'github' =>
                        "Sorry you are not in the org list :(.
                        Make sure you are in the proper organization for this
                    GitHub base authentication and that you are logged into GitHub",
                ]);
            }
        }
    ]);


    /**
     * Autentication Manager
     */

    Route::group(
        ['middleware' => 'auth'],
        function () {
            Route::get(
                'admin/github-team-auth',
                [
                    'uses' => '\Friendsofcat\GitHubTeamAuth\Controllers\GithubTeamAuthSettingsController@getSettings',
                    'as' => 'github-team-auth.index'
                ]
            );

            Route::get(
                'admin/github-team-auth/add/org',
                [
                    'uses' => '\Friendsofcat\GitHubTeamAuth\Controllers\GithubTeamAuthOrgController@addOrg',
                    'as' => 'github-team-auth.add_org'
                ]
            );

            Route::post(
                'admin/github-team-auth/store/org',
                [
                    'uses' => '\Friendsofcat\GitHubTeamAuth\Controllers\GithubTeamAuthOrgController@storeOrg',
                    'as' => 'github-team-auth.store_org'
                ]
            );

            Route::delete(
                'admin/github-team-auth/org/{org_id}',
                [
                    'uses' => '\Friendsofcat\GitHubTeamAuth\Controllers\GithubTeamAuthOrgController@deleteOrg',
                    'as' => 'github-team-auth.org.delete'
                ]
            );


            Route::get(
                'admin/github-team-auth/add/team',
                [
                    'uses' => '\Friendsofcat\GitHubTeamAuth\Controllers\GithubTeamAuthTeamController@addTeam',
                    'as' => 'github-team-auth.add_team'
                ]
            );

            Route::post(
                'admin/github-team-auth/add/team',
                [
                    'uses' => '\Friendsofcat\GitHubTeamAuth\Controllers\GithubTeamAuthTeamController@storeTeam',
                    'as' => 'github-team-auth.store_team'
                ]
            );

            Route::delete(
                'admin/github-team-auth/team/{team_id}',
                [
                    'uses' => '\Friendsofcat\GitHubTeamAuth\Controllers\GithubTeamAuthTeamController@deleteTeam',
                    'as' => 'github-team-auth.team.delete'
                ]
            );
        }
    );

});
