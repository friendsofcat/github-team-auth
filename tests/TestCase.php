<?php

namespace Tests;

use GrahamCampbell\GitHub\Facades\GitHub;
use GrahamCampbell\GitHub\GitHubServiceProvider;
use Illuminate\Support\Facades\DB;
use Laravel\Socialite\Facades\Socialite;
use Friendsofcat\GitHubTeamAuth\GitHubAuthCore\GitHubAuthService;
use Friendsofcat\GitHubTeamAuth\GitHubTeamAuthProvider;
use Orchestra\Testbench\TestCase as Testbench;

class TestCase extends TestBench
{
    /**
     * Define environment setup.
     *
     * @param \Illuminate\Foundation\Application $app
     */
    protected function getEnvironmentSetUp($app)
    {
        // Setup default database to use sqlite :memory:
        $app['config']->set('database.default', 'testbench');
        $app['config']->set('githublogin.team_table_name', 'teams');
        $app['config']->set('githublogin.custom_redirect', '/');
        $app['config']->set('database.connections.testbench', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);

        $app['config']->set('session.driver', 'database');
        $app['config']->set('logging.channels.single.path', __DIR__ . '/logs/laravel.log');
    }

    /**
     * getPackageProviders.
     *
     * @param App $app
     *
     * @return array
     */
    protected function getPackageProviders($app)
    {
        return [
            GitHubTeamAuthProvider::class,
            \Laravel\Socialite\SocialiteServiceProvider::class,
            GitHubServiceProvider::class
        ];
    }

    /**
     * Get package aliases.  In a normal app environment these would be added to
     * the 'aliases' array in the config/app.php file.  If your package exposes an
     * aliased facade, you should add the alias here, along with aliases for
     * facades upon which your package depends, e.g. Cartalyst/Sentry.
     *
     * @param  \Illuminate\Foundation\Application  $app
     *
     * @return array
     */
    protected function getPackageAliases($app)
    {
        return [
            'Socialite' => \Laravel\Socialite\Facades\Socialite::class,
            'GitHub' => GitHub::class
        ];
    }

    /**
     * Setup the test environment.
     */
    public function setUp()
    {
        parent::setUp();

        $this->loadLaravelMigrations(['--database' => 'testbench']);

        $this->withFactories(__DIR__.'/../database/factories');

        $this->loadMigrationsFrom([
            '--database' => 'testbench',
            '--path' => realpath(__DIR__.'/../database/migrations'),
        ]);
        
    }
}
