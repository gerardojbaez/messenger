<?php

namespace Gerardojbaez\Messenger\tests\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Gerardojbaez\Messenger\Contracts\MessageableInterface;
use Gerardojbaez\Messenger\Traits\Messageable;

class User extends Authenticatable implements MessageableInterface
{
    use Messageable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];
}
