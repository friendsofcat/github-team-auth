<?php

namespace Friendsofcat\GitHubTeamAuth;

use Illuminate\Database\Eloquent\Model;

class TeamGrants extends Model
{

    /**
     * Creates a new instance of the model.
     */
    public function __construct(array $attributes = [ ])
    {
        parent::__construct($attributes);
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id',
        'grant_name',
    ];
}
