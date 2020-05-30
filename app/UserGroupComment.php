<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * Record of each upgrade with their comments
 */
class UserGroupComment extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'users_groups_comments';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     *
     */
    protected $fillable = ['users_id','groups_id','test_id','updated_at','updated_by', 'comments'];
}
