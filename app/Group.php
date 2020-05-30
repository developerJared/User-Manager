<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'groups';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     *
     */
    protected $fillable = ['group'];

    public function users()
    {
        return $this->belongsToMany('App\User', 'users_groups', 'groups_id', ['users_id','id']);
    }

    public static function getFunctionsArray(){
        return Group::where('group_type',"Calibration" )->orWhere('group_type',"Operator")->orWhere('group_type',"Report")->get()->toArray();
    }

    /***
     * @param $groupName string name of the group to get id for
     * @return integer
     */
    public static function getId($groupName){
        $group = Group::where('group',$groupName)->first();
        return $group->id;
    }
}
