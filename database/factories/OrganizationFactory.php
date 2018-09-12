<?php

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| Here you may define all of your model factories. Model factories give
| you a convenient way to create models for testing and seeding your
| database. Just tell the factory how a default model should look.
|
*/

$factory->define(\Friendsofcat\GitHubTeamAuth\Organization::class, function (Faker\Generator $faker) {

    return [
        'id'    => $faker->uuid,
        'org_name' => $faker->name,
    ];
});

$factory->define(\Friendsofcat\GitHubTeamAuth\Team::class, function (Faker\Generator $faker) {

    return [
        'id'    => $faker->uuid,
        'team_name' => $faker->name,
        'acl'       => $faker->word,
    ];
});
