<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserView extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id', 'suggestion_id', 'views'
    ];

	
}
