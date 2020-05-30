<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\TestType;
use App\UserGroup;
use Carbon\Carbon;
use Illuminate\Http\Request;
use DB;
use GuzzleHttp;
use App\User;
use App\Group;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Base\ConfigurationController;

class AccessController extends Controller
{
    public function __construct()
    {
        //$this->middleware('jwt.auth', ['except' => ['index']]);
    }
//TODO: CHECK THE TESTACCEESS CHECK
    /****
     * @param $users array can serve many users to one group
     * @param $groupID integer id of group to be added to
     * @param $supervisor string optional when called directly can assign 'updated_by' field.
     * @return mixed
     */
    public function assignToGroup(Request $request)
    {
        $input = $request->all();
        if (!array_key_exists("users", $input) && !array_key_exists("group", $input)) {
            return response()->json(array('result' => false, 'error' => "Requires: users as array AND a group Id."), 400);
        }
       // return response()->json(array('result' => true), 200);
        try {
            $userGroup = new UserGroup();
                $userID = $input['users'];
                $userGroup->users_id = $userID;
                $userGroup->groups_id = $input['group'];
                $userGroup->updated_by = (array_key_exists("supervisor", $input)) ? $input['supervisor'] : "Support";
                $userGroup->test_id = 0;
                $userGroup->save();

            return response()->json(array('result' => true), 200);
        } catch (\Exception $e) {
            return response()->json(array('result' => false, 'error' => $e->getMessage()), 500);
        }
    }


    public function bulkResetCutters(Request $request)
    {
        $input = $request->all();

        try {
            UserGroup();
            return response()->json(array('result' => true), 200);
        } catch (\Exception $e) {
            return response()->json(array('result' => false, 'error' => $e->getMessage()), 500);
        }
    }

    /****
     * @param $users array can remove many users from one group
     * @param $groupID integer id of group to be removed from
     * @return mixed
     */
    public function removeFromGroup(Request $request)
    {
        $input = $request->all();

        try{
          UserGroup::find($input['relation_id'])->delete();
            return response()->json(array('result' => true), 200);
        } catch (\Exception $e) {
            return response()->json(array('result' => false, 'error' => $e->getMessage()), 500);
        }
    }

    /***
     * Assigns initial test access permissions
     * that can me later manipulated
     * @param $users array one or more id's for intial assignments
     * @return mixed
     */
    public function assignInitialTestCompetency(Request $request)
    {
        $input = $request->all();
        try {
            $testTypes = TestType::all()->toArray();
            foreach ($input['users'] as $userID) {
                foreach ($testTypes as $testType) {
                    if (!UserGroup::where(['users_id' => $userID, 'test_id' => $testType['id']])->first()) {
                        $userGroup = new UserGroup();
                        $userGroup->users_id = $userID;
                        $userGroup->groups_id = 1;
                        $userGroup->test_id = $testType['id'];
                        $userGroup->updated_by = "Support";
                        $userGroup->save();
                    }
                }
            }
            return response()->json(array('result' => true), 200);
        } catch (\Exception $e) {
            return response()->json(array('result' => false, 'error' => $e->getMessage()), 500);
        }
    }
//TODO:: REVIEW THESE INCREMENT AND DECREMENT PROCESSES
    public function incrementTestCompetency(Request $request)
    {
        $input = $request->all();
        try {
            $userGroup = UserGroup::where(['users_id' => $input['user'], 'test_id' => $input['test']])->first();
           if($userGroup) {
               if ($userGroup->groups_id <= 3) {
                   UserGroup::where(['users_id' => $input['user'], 'test_id' => $input['test']])
                       ->update(['groups_id' => ++$userGroup->groups_id, 'updated_by' => (array_key_exists("supervisor", $input)) ? $input['supervisor'] : "Support"]);
               }
               return response()->json(array('result' => true), 200);
           }else{
               //UserGroup::where(['users_id' => $input['user'], 'test_id' => $input['test']])->first();
               try {
                   $ug = new UserGroup;
                   $ug->users_id = $input['user'];
                   $ug->test_id = $input['test'];
                   $ug->groups_id = 2;
                   $ug->updated_at = Carbon::now();
                   $ug->updated_by = (array_key_exists("supervisor", $input)) ? $input['supervisor'] : "Support";
                   $ug->save();
                   return response()->json(array('result' => true), 200);
               } catch (\Exception $e){
                   return response()->json(array('result' => false, 'error' => $e->getMessage()), 500);
               }
           }
        } catch (\Exception $e) {
            return response()->json(array('result' => false, 'error' => $e->getMessage()), 500);
        }
    }

    public function decrementTestCompetency(Request $request)
    {
        $input = $request->all();
        try {
            $userGroup = UserGroup::where(['users_id' => $input['user'], 'test_id' => $input['test']])->first();
            if (($userGroup->groups_id <= 4) && ($userGroup->groups_id >= 2)) {
                UserGroup::where(['users_id' => $input['user'], 'test_id' => $input['test']])
                    ->update(['groups_id' => --$userGroup->groups_id, 'updated_by' => (array_key_exists("supervisor", $input)) ? $input['supervisor'] : "Support"]);
            }
            return response()->json(array('result' => true), 200);
        } catch (\Exception $e) {
            return response()->json(array('result' => false, 'error' => $e->getMessage()), 500);
        }
    }

    public function resetTestCompetency(Request $request)
    {
        $input = $request->all();
        try {
            UserGroup::where(['users_id' => $input['user'], 'test_id' => $input['test']])
                ->update(['groups_id' => 1, 'updated_by' => (array_key_exists("supervisor", $input)) ? $input['supervisor'] : "Support"]);
            return response()->json(array('result' => true), 200);
        } catch (\Exception $e) {
            return response()->json(array('result' => false, 'error' => $e->getMessage()), 500);
        }
    }

    public function bulkResetTestCompetency(Request $request)
    {
        $input = $request->all();
        $users = $input['users'];
        try{
            foreach($users as $user) {
                UserGroup::where(['users_id' => $user['id']])->where('test_id' ,'!=',0)
                    ->update(['groups_id' => 1, 'updated_by' => (array_key_exists("supervisor", $input)) ? $input['supervisor'] : "Support"]);
            }
            return response()->json(array('result' => true), 200);
        }catch (\Exception $e) {
            return response()->json(array('result' => false, 'error' => $e->getMessage()), 500);
        }

    }

    /*********************************
     * USED FOR INFORMATIONAL TABLES
     ********************************/

    public function getNumUsersForGroup($group_id)
    {
        $numUsers = UserGroup::where(['groups_id' => $group_id, 'test_id' => 0])->count();
        return response()->json(array('result' => $numUsers), 200);
    }

    public function getNumUsersAssignedToTest()
    {
        $numUsers = UserGroup::distinct('users_id')->where('test_id', '>=', 1)->count('users_id');
        return response()->json(array('result' => $numUsers), 200);
    }


}
