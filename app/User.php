<?php

namespace App;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Log;
use Hash;
use Carbon\Carbon;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;

class User extends Model implements AuthenticatableContract,
                                    AuthorizableContract,
                                    CanResetPasswordContract
{
    use Authenticatable, Authorizable, CanResetPassword;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'users';
    protected $primaryKey = "id";
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'firstName',
        'lastName',
        'address',
        'phone',
        'mobile',
        'pin',
        'current_lab',
        'username',
        'password',
        'roles_id',
        'legacy_staff_type_id',
        'legacy_user_id',
        'legacy_staff_id',
        'active',
        'administrator',
        'admin_enabled'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    //protected $hidden = ['password'];

    protected $dates = ['deleted_at'];

    public function groups()
    {
        return $this->belongsToMany('App\Group', 'users_groups', 'users_id', 'groups_id')->withPivot('test_id','updated_at','updated_by')->withPivot('id');
    }

    //TODO: Add common static functions like users by group or role
    /********************************************
     * COMMON CRUD FUNCTIONS USED THROUGHOUT API
     *******************************************/

    /*public function store(){

    }
    public function update(){

    }
    public function deactivate(){

    }*/

    /********************************************
     * Used mainly in import controller for users
     * @param $user
     * @param bool $support
     *******************************************/
    public static function storeImported($user, $support = false){
        if ($user->Pin != null) {
            $savePin = Hash::make($user->Pin, array('rounds' => 4));
        } else {
            $savePin = $user->Pin;
        }

        $saveUser = [];
        if($support){
            $saveUser['nl_name'] = "NetlabUser:" . $user->FirstName . " " . $user->LastName;
            $saveUser['firstName'] = $user->FirstName;
            $saveUser['lastName'] = $user->LastName;
            $saveUser['address'] = $user->address;
            $saveUser['phone'] = $user->Landline;
            $saveUser['mobile'] = $user->MobilePhone;
            $saveUser['username'] = trim($user->FirstName).trim(substr($user->LastName, 0,1));
            $saveUser['legacy_staff_type_id'] = $user->StaffCode;
            $saveUser['legacy_staff_id'] = $user->StaffID;
            $saveUser['password'] = Hash::make("Support".$user->FirstName, array('rounds' => 4));
            $saveUser['pin'] = $savePin;
            $saveUser['active'] = 1;
            $saveUser['administrator'] = 1;
            $saveUser['admin_enabled'] = 1;
            $saveUser['roles_id'] = 5;
        }else {
            $saveUser['nl_name'] = "NetlabUser:" . $user->FirstName . " " . $user->LastName;
            $saveUser['firstName'] = $user->FirstName;
            $saveUser['lastName'] = $user->LastName;
            $saveUser['address'] = $user->address;
            $saveUser['phone'] = $user->Landline;
            $saveUser['mobile'] = $user->MobilePhone;
            $saveUser['username'] = trim($user->FirstName).trim(substr($user->LastName, 0,1));
            $saveUser['legacy_staff_type_id'] = $user->StaffCode;
            $saveUser['legacy_staff_id'] = $user->StaffID;
            $saveUser['password'] = Hash::make("test", array('rounds' => 4));
            $saveUser['pin'] = $savePin;
            $saveUser['active'] = 1;
            if ($user->isSupervisor === 1) {
                $saveUser['roles_id'] = 5;
            } else {
                $saveUser['roles_id'] = 3;
            }
        }
        User::create($saveUser);
        Log::info("Created " . $user->FirstName);
        $accessUser = User::where(['firstName' => $user->FirstName, 'lastName' => $user->LastName, 'legacy_staff_type_id' => $user->StaffCode])->get()->first();
        User::setNewAccess($accessUser->id, $support);
    }

    /****************************************
     * UPDATE IMPORTED USERS
     * @param $user
     **************************************/
    public static function updateImported($user){
        Log::info("Updating: " . $user->FirstName);

        if ($user->Pin != null) {
            $savePin = Hash::make($user->Pin, array('rounds' => 4));
        } else {
            $savePin = $user->Pin;
        }
        if ($user->isSupervisor === 1) {
            $role = 5;
        } else {
            $role = 3;
        }
        User::where(['firstName' => $user->FirstName, 'lastName' => $user->LastName, 'legacy_staff_type_id' => $user->StaffCode])->
        update([
            'nl_name' => "NetlabUser:" . $user->FirstName . " " . $user->LastName,
            'firstName' => $user->FirstName,
            'lastName' => $user->LastName,
            'address' => $user->address,
            'phone' => $user->Landline,
            'mobile' => $user->MobilePhone,
            'username' => trim($user->FirstName).trim(substr($user->LastName, 0,1)),
            'active' => 1, //IF they are active coming through then they are active in the appliance
            'pin' => $savePin,
            'legacy_staff_type_id' => $user->StaffCode,
            'legacy_staff_id' => $user->StaffID,
            'roles_id' => $role
        ]);
        Log::info("Updated " . $user->FirstName);
    }

    /**********************************************************************
     * DeActivate Users that have not come over as the ONLY way to create
     * Since currently the only way to create users is through
     * Company website, anyone not coming over will
     * be considered inactive.
     *********************************************************************/
    public static function deactivateImported($activeUsers){
        $deactiveUsers = User::whereNotIn('legacy_staff_id', $activeUsers)->get();
        foreach($deactiveUsers as $u){
            //If active , deactivate as long as not admin (for support users and permanent staff)
            if($u->active == 1 && $u->administrator != 1){
                User::where('legacy_staff_id',$u->legacy_staff_id)->update(['active' => 0]);
                Log::warning("This user was deactivated because they are inactive or do not have a pin number");
                Log::warning($u->firstName." ".$u->lastName);
            }
        }
    }

    /**********************************************************************
     * DeActivate Support Users that are not in the file
     *********************************************************************/
    public static function deactivateSupportImported($activeUsers){
        $deactiveUsers = User::whereNotIn('legacy_staff_id', $activeUsers)->get();
        foreach($deactiveUsers as $u){
            //If active , deactivate as long as not admin (for support users and permanent staff)
            if($u->active == 1 && $u->administrator == 1 && $u->admin_enabled == 1){
                User::where('legacy_staff_id',$u->legacy_staff_id)->update(['active' => 0]);
                Log::warning("This user was deactivated because they are inactive or do not have a pin number");
                Log::warning($u->firstName." ".$u->lastName);
            }
        }
    }

    /*********************************
     * HELPER - SETS TEST ACCESS FOR NEW USERS
     ********************************/
    public static function setNewAccess($usersID, $support = false)
    {
        $tests = TestType::all();
        $groups = Group::all();
        if($support){
            foreach ($tests as $test) {
                $UserGroups = new UserGroup();
                $UserGroups->test_id = $test['id'];
                $UserGroups->users_id = $usersID;
                $UserGroups->groups_id = 4;
                $UserGroups->save();
            }
            foreach($groups as $group){
                if($group['id'] > 4){
                    $UserGroups = new UserGroup();
                    $UserGroups->test_id = 0;
                    $UserGroups->users_id = $usersID;
                    $UserGroups->groups_id = $group['id'];
                    $UserGroups->save();
                }
            }
        }else {
            foreach ($tests as $test) {
                $UserGroups = new UserGroup();
                $UserGroups->test_id = $test['id'];
                $UserGroups->users_id = $usersID;
                $UserGroups->groups_id = 1;
                $UserGroups->created_at = Carbon::now();
                $UserGroups->updated_at = Carbon::now();
                $UserGroups->save();
            }
        }
        Log::info("Set Access for: " . $usersID);
    }


}
