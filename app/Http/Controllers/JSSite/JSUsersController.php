<?php

namespace App\Http\Controllers\JSSite;

use App\Http\Controllers\Controller;
use App\UserGroup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use App\Http\Controllers\Base\ConfigurationController;
use App\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Hash;


class JSUsersController extends Controller
{

    public function __construct()
    {
        //$this->middleware('jwt.auth', ['except' => ['index']]);
    }

    /*******************************************************************************
     * USER CRUD
     ******************************************************************************/

    public function get($id = null){
        if ($id != null) {
            $user = User::where('id', $id)->with('groups')->first();
            //dd($user);
            $tempUser = (object)[
                'id' => $user->id,
                'firstName' => $user->firstName,
                'lastName' => $user->lastName,
                'address' => $user->address,
                'phone' => $user->phone,
                'mobile' => $user->mobile,
                'pin' => $user->pin,
                'current_lab' => $user->current_lab,
                'username' => $user->username,
                'password' => $user->password,
                'roles_id' => $user->roles_id,
                'legacy_staff_type_id' => $user->legacy_staff_type_id,
                'legacy_user_id' => $user->legacy_user_id,
                'legacy_staff_id' => $user->legacy_staff_id,
                'active' => $user->active
            ];
            $tempUser->{'tests'} = new \stdClass();
            $tempUser->{'groups'} = new \stdClass();
            foreach ($user->groups as $group) {
                if ($group->pivot->test_id == 0) {
                    $tempUser->{'groups'}->{$group->id} = (object)[
                        'group' => $group->group,
                        'group_type' => $group->group_type,
                        'updated_at' => $group->pivot->updated_at,
                        'updated_by' => $group->pivot->updated_by,
                        'relation_id' => $group->pivot->id
                    ];
                } else {
                    $tempUser->{'tests'}->{$group->pivot->test_id} = (object)[
                        'group_id' => $group->id,
                        'group' => $group->group,
                        'updated_at' => $group->pivot->updated_at,
                        'updated_by' => $group->pivot->updated_by,
                        'relation_id' => $group->pivot->id
                    ];
                }
            }
            return response()->json($tempUser, 200);
        }
        $users = User::with('groups')->get();
        $result = [];
        foreach ($users as $user) {
            $tempUser = (object) [
            'id'                    => $user->id,
            'firstName'             => $user->firstName,
            'lastName'              => $user->lastName,
            'address'               => $user->address,
            'phone'                 => $user->phone,
            'mobile'                => $user->mobile,
            'pin'                   => $user->pin,
            'current_lab'           => $user->current_lab,
            'username'              => $user->username,
            'password'              => $user->password,
            'roles_id'              => $user->roles_id,
            'legacy_staff_type_id'  => $user->legacy_staff_type_id,
            'legacy_user_id'        => $user->legacy_user_id,
            'legacy_staff_id'       => $user->legacy_staff_id,
            'active'                => $user->active
            ];
            $tempUser->{'tests'} = new \stdClass();
            $tempUser->{'groups'} = new \stdClass();
            foreach ($user->groups as $group) {
                if($group->pivot->test_id == 0){
                    $tempUser->{'groups'}->{$group->id} = (object) [
                        'group' => $group->group,
                        'group_type' => $group->group_type,
                        'updated_at' => $group->pivot->updated_at,
                        'updated_by' => $group->pivot->updated_by,
                        'relation_id' => $group->pivot->id
                    ];
                }else{
                    $tempUser->{'tests'}->{$group->pivot->test_id} =  (object) [
                        'group_id' => $group->id,
                        'group' => $group->group,
                        'updated_at' => $group->pivot->updated_at,
                        'updated_by' => $group->pivot->updated_by,
                        'relation_id' => $group->pivot->id
                    ];
                }
            }
           array_push($result,$tempUser);
        }
        return response()->json($result, 200);

    }

    public function createUser(Request $request)
    {
        $input = $request->all();
        $userChecks = [
            'firstName'=>$input['firstName'],
            'lastName'=>$input['lastName'],
            'username'=>$input['username'],
        ];
        $checkUser = User::where($userChecks)->first();
        if ($checkUser != null) {
            return response()->json(array('result' => false,'error' => "User Already Exists or Choose a different Username "), 500);
        }

        try {
            $user = [];
            $user['firstName'] = $input['firstName'];
            $user['lastName'] = $input['lastName'];
            $user['address'] = $input['address'];
            $user['phone'] = $input['phone'];
            $user['mobile'] = $input['mobile'];
            $user['username'] = $input['username'];
            $user['password'] = Hash::make($input['password'],array('rounds'=>4));
            $user['roles_id'] = (int)$input['roles_id'];

            if ($this->checkUniquePIN($input['pin'])) {
                $user['pin'] = Hash::make($input['pin'],array('rounds'=>4));
            } else {
                return response()->json(array('result' => false,'error' => 'Pin Already in use.'), 500);
            }
            //assigns the current lab at creation to the lab that created user.
            $user['current_lab'] = ConfigurationController::getLocalLab();
            User::create($user);
            return response()->json(array('result' => true), 200);

        } catch (\Exception $e) {
            return response()->json(array('result' => false,'error' => $e->getMessage()), 500);
        }
    }

    public function deleteUser($id){
        try{
            User::where('id',$id)->delete();
            return response()->json(array('result' => true), 200);
        } catch (\Exception $e){
            return response()->json(array('result' => false,'error' => $e->getMessage()), 500);
        }
    }

    public function update(Request $request)
    {
        $reqData = $request->all();
        $activeBox = $request['active'];
        $userID = $reqData['id'];
        $user = User::find($userID);

        try {
            $user->id = $userID;
            $user->nl_name = "NetlabUser:" . $reqData['firstName'] . " " . $reqData['lastName'];
            $user->firstName = $reqData['firstName'];
            $user->lastName = $reqData['lastName'];
            $user->address = $reqData['address'];
            $user->phone = $reqData['phone'];
            $user->mobile = $reqData['mobile'];
            $user->current_lab = $reqData['current_lab'];

            if ($reqData['username'] == null) {
                $user->username = $reqData['username'];
            }

            if (array_key_exists('password', $reqData) || $reqData['password'] != null) {
                $user->password = Hash::make($reqData['password'], array('rounds' => 4));
            }

            if(array_key_exists('pin', $reqData) && $reqData['pin'] != null){ //Unique pin : EFK requirement
                if ($this->checkUniquePIN($reqData['pin'])) {
                    $user->pin = Hash::make($reqData['pin'], array('rounds' => 4));
                } else {
                    return response()->json(array('request' => $reqData, 'result' => false, 'error' => 'Pin already in use.'), 500);
                }
            }

            if ($reqData['roles_id'] == null) {
                $user->roles_id = (int)$reqData['roles_id'];
            }

            //Check the Active Checkbox
            if ($activeBox != null) {
                $user->active = 1;
            } else {
                $user->active = 0;
            }
            $user->save();

        } catch (\Exception $e) {
            return response()->json(array('request' => $reqData,'result' => false,'error' => $e->getMessage()), 500);
        }
    }

    public function storeStaffAccess(Request $request)
    {
        $input = $request->all();
        try {
            foreach ($input as $key => $value) {
                $loggedin = Session::get('user');
                $loggedinName = $loggedin['firstName'] . " " . $loggedin['lastName'];
                if (is_numeric($key) && $value != null && !$this->checkUserTestGroup($input['id'], $key)) {
                    UserGroup::insert(['users_id' => $input['id'], 'groups_id' => $value, 'test_id' => $key, 'updated_by' => $loggedinName]);
                } elseif (is_numeric($key) && $value != null && $this->checkUserTestGroup($input['id'], $key)) {
                    $ug = UserGroup::where(['users_id' => $input['id'], 'test_id' => $key])->first();
                    if ($ug['groups_id'] != $value) {
                        UserGroup::where(['users_id' => $input['id'], 'test_id' => $key])->update(['groups_id' => $value, 'updated_by' => $loggedinName]);
                    }
                }
            }
            $this->appendBulkWeightGroup($input['id']);
            return response()->json(array('result' => true), 200);
        } catch (\Exception $e) {
            return response()->json(array('request' => $request,'result' => false,'error' => $e->getMessage()), 500);
        }
    }

    public function appendBulkWeightGroup($userID)
    {
        $ug = UserGroup::where(['users_id' => $userID, 'test_id' => 7])->where('groups_id', '>', 1)->first();

        if ($ug != null && $ug != []) {
            $this->addUserGroup($userID, 5);
        } else {
            $this->removeUserGroup($userID, 5);
        }
    }

    public function storeFunctionsAccess(Request $request)
    {
        $input = $request->all();

        /***************************
         *BASE ARRAY FOR USERS
         **************************/
        $users_id_array = [];
        $base_array = User::all(['id'])->toArray();
        foreach ($base_array as $key => $value) {
            array_push($users_id_array, $value['id']);
        }

        /***************************
         *ADDING TO USERS_GROUPS TABLE
         * REMOVING FROM BASE ARRAYs
         **************************/
        foreach ($input as $key => $value) {
            if (in_array($key, $users_id_array)) {
                try {
                    $this->addUserGroup($key, $input['target_function']);
                    if (($key = array_search($key, $users_id_array)) !== false) {
                        unset($users_id_array[$key]);
                    }
                } catch (\Exception $e) {
                    return $e;
                }
            }
        }

        /***************************
         *REMOVING FROM USERS_GROUPS TABLE
         * BASED ON WHATS LEFT OF BASE ARRAY
         **************************/
        foreach ($users_id_array as $key => $value) {
            if ($users_id_array != null && $users_id_array != [] && $value != null) {
                try {
                    $this->removeUserGroup($value, $input['target_function']);
                } catch (\Exception $e) {
                    return $e;
                }
            }
        }

    }

    /********************************************************************
     * UTILS FUNCTIONS SECTION
     ********************************************************************/
    public function addUserGroup($userID, $groupID)
    {
        try {
            if (!$this->checkUserGroup($userID, $groupID)) {
                $loggedin = Session::get('user');
                $loggedinName = $loggedin['firstName']." ".$loggedin['lastName'];
                UserGroup::insert(['users_id' => $userID, 'groups_id' => $groupID, 'updated_by'=>$loggedinName]);
            }
        } catch (\Exception $e) {
            return $e;
        }
    }

    public function removeUserGroup($userID, $groupID)
    {
        try {
            if ($this->checkUserGroup($userID, $groupID)) {
                UserGroup::where(['users_id' => $userID, 'groups_id' => $groupID])->delete();
            }
        } catch (\Exception $e) {
            return $e;
        }
    }

    public function checkUserGroup($userID, $groupID)
    {
        $ug = UserGroup::where('users_id', $userID)->get()->pluck('groups_id')->all();
        if (in_array($groupID, $ug)) {
            return true;
        } else {
            return false;
        }
    }

    public function checkUserTestGroup($userID, $testID)
    {
        $ug = UserGroup::where(['users_id' => $userID, 'test_id' => $testID])->first();
        if ($ug != null) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Returns false if the given pin is already in use as if
     * CANNOT be used and makes if statement cleaner.
     * @param $givenPin
     * @return bool
     */
    public function checkUniquePIN($givenPin)
    {
        $users = DB::table('users')->lists('pin');
        $available = "true";
        foreach ($users as $user) {
            if (Hash::check((string)$givenPin, (string)$user)) {
                $available = "false";
            }
        }
        return $available;
    }


}
