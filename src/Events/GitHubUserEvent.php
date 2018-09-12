<?php


namespace Friendsofcat\GitHubTeamAuth\Events;

use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;

class GitHubUserEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @var \Laravel\Socialite\Two\User $quality_report
     */
    public $user_github_object;


    /**
     * @var array $quality_report
     */
    public $user_team_array;


    /**
     * Create the event listener.
     *
     * @var \Laravel\Socialite\Two\User $user
     * @var array $user_team_array
     */
    public function __construct($user, $user_team_array)
    {
        $this->user_github_object = $user;
        $this->user_team_array = $user_team_array;
    }
}
