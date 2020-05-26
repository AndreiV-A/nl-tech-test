<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\BoxFile;
use Faker\Generator as Faker;
use Illuminate\Support\Str;

$factory->define(BoxFile::class, function (Faker $faker) {
    return [
        'box_id' => Str::random(20),
    ];
});