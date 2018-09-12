<?php

namespace Friendsofcat\GitHubTeamAuth;

use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;

class GitHubTeamAuthProvider extends ServiceProvider
{
    public function boot()
    {

        $this->loadRoutesFrom(__DIR__.'/../routes.git.php');

        $this->publishes([
            __DIR__.'/../config/githublogin.php' => config_path('githublogin.php'),
        ], 'github_team_auth:config');
        
        $this->publishes([
            __DIR__.'/../database/migrations' => base_path('database/migrations'),
        ], 'github_team_auth:migrations');


        $this->loadViewsFrom(__DIR__ . '/../views', 'github-team-auth');

        $this->publishes([
            __DIR__ . '/../views/' => base_path('resources/views/vendor/github-team-auth')
        ], 'github_team_auth:views');
    }
}
