<?php

namespace Zerquix18\LaraActions;

use Illuminate\Database\Eloquent\Model;

class Action extends Model
{
    protected $fillable = ['user_id', 'action_name'];
}
