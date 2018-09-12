<?php

namespace Friendsofcat\GitHubTeamAuth;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;

class Team extends Model
{
    public $incrementing = false;

    /**
     * Creates a new instance of the model.
     *
     * @param array $attributes
     */
    public function __construct(array $attributes = [ ])
    {
        parent::__construct($attributes);
        $this->table =  Config::get('githublogin.team_table_name');
    }

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id',
        'team_name',
        'acl',
    ];
}
