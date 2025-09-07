<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    protected $fillable = ['name'];

    public function results()
    {
        return $this->hasMany(Result::class);
    }
}
