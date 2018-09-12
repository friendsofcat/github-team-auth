# GitHubLogin


## Overview

The GitHubLogin is a Laravel package which uses the socialite(https://github.com/laravel/socialite) to login a user by getting his github user account and permission.
It basically get a github account and validate if the use is part of the expected organization and teams, and send a event with the user data.
The expected organization and teams must be populated in database, which is possible to do by UI (See the UI section for more information).

## Requirements

~~~
"require": {
    "php": ">=5.5.9",
    "illuminate/support": "5.*",
    "laravel/socialite": "^3.0@dev",
    "knplabs/github-api": "^2.9",
    "php-http/guzzle6-adapter": "^1.1",
    "graham-campbell/github": "^7.4"
  }
~~~


## Install

Composer install

~~~
composer require friendsofcat/github-team-auth
~~~

Publish config File


~~~
 php artisan vendor:publish 
~~~

Publish config file

~~~
php artisan vendor:publish --provider="Friendsofcat\GitHubTeamAuth\GitHubTeamAuthProvider" --tag='github_team_auth:config'
~~~

The config file has only onw parameter, team_table_name, whihc define the name of the table in database for githubteams names, the default value is 'teams'


Publish migrations

~~~
php artisan vendor:publish --provider="Friendsofcat\GitHubTeamAuth\GitHubTeamAuthProvider" --tag='github_team_auth:migrations'
~~~

Publish views

~~~
php artisan vendor:publish --provider="Friendsofcat\GitHubTeamAuth\GitHubTeamAuthProvider" --tag='github_team_auth:views'
~~~


Edit app/config/service.php to add the code below
~~~
    'github' => [
        'client_id' => env('GITHUB_CLIENT_ID'),
        'client_secret' => env('GITHUB_CLIENT_SECRET'),
        'redirect' =>   'https://cat-quality-service.test/auth/github/callback',
    ],
~~~


Env file
For service.php configurations is necessary to inform:

- GITHUB_CLIENT_ID
- GITHUB_CLIENT_SECRET
- redirect ( whihc must be  https://{{APP_NAME}}/auth/github/callback ).

You can get all these parameters after register the app in the github callback feature.

## View to Manage Organization and orgs

### https://{domain}/admin/github-team-auth
This is the dashboard page which list all the team and orgs from this page you are able to delete an organization or team. Also you can navaguete to create team or create organization screen.

### https://{domain}/admin/github-team-auth/add/team
This page was design for add a new team, display the team possibilities using the github token storaged in cache when user logged in.
Also allow the user add a tag for label the access level of team, it can be add by the 'acl' field.  

### https://{domain}/admin/github-team-auth/add/org
This page was design for add a new team, display the organization possibilities using the github token storaged in cache when user logged in.

## Event 
In the end of process the user will be login and an event will be trigger.
This event has to public parameters:

- user_github_object ( \Laravel\Socialite\Two\User )

        Which contain all the user information.

- user_team_array ( array )

        An array with all the teams which this user is part of.


## HTML
You must add the git hub loggin button in your login page, as the example below:

~~~
<a class="btn mb-2 btn-secondary"  href="{{ route('git_auth') }}">
    <i class="fa fa-github"></i>&nbsp;GitHub
</a>

@if ($errors->has('github'))
    <span class="help-block alert alert-dark">
        <strong>{{ $errors->first('github') }}</strong>
    </span>
@endif
~~~


## Chicken - Egg dilemma

For add a new Organization/Team the user need to be logged at the system, but for loggin the system must have some vaid organization and team in DB.
To solve this problem create a migration and insert the basic organization and team.