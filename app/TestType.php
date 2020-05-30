<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TestType extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'test_types';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     *
     */
    protected $fillable = ['name'];

    /*public function groups()
    {
        return $this->belongsToMany('App\Group', 'users_groups', 'users_id', 'groups_id');
    }*/


}
