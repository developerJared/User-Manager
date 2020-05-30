<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class RoleGroup extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'roles_groups';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     *
     */
    protected $fillable = ['roles_id','groups_id'];
}
