<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ApiToken extends Model
{
    protected $fillable = [
        'service', 'access_token', 'refresh_token', 'scope', 'type', 'expiry'
    ];
}
