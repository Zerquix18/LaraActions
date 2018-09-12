<?php

namespace Zerquix18\LaraActions;

use Illuminate\Database\Eloquent\Model;

class Ban extends Model
{
    protected $fillable = ['user_id', 'action_name', 'until'];
}
