<?php

namespace Friendsofcat\GitHubTeamAuth;

use Illuminate\Database\Eloquent\Model;

class Organization extends Model
{
    public $incrementing = false;
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id',
        'org_name',
    ];
}
