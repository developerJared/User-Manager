<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\TestType;
use App\UserGroup;
use Illuminate\Http\Request;
use GuzzleHttp;
use App\User;
use App\UserGroupComment;
use App\Group;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Base\ConfigurationController;

class UsersController extends Controller
{
    private $samplerGroup;
    private $labTechGroup;
    public function __construct()
    {
        /*$this->middleware('jwt.auth', ['except' => [
            'index',
            'getByPin',
            'getBareUsers',
            'getByGroup',
            'getByTestGroup',
            'isUserInGroup',
            'getSamplers',
            'changeAccessLevel',
            'getUserGroup',
            'amqp']]);
        */

        $this->samplerGroup = Group::where('group','Sampler')->first();
        $this->labTechGroup = Group::where('group','LabTech')->first();
    }

    /*********************
     * User CRUD
     ********************/
    public function get($id = null){
        if($id){
            return User::where('id',$id)->first();
        }
        return User::all();
    }

    public function createUser(User $user)
    {
        User::create($user);
    }

    public function store(Request $request)
    {
        $input = $request->all();
        $user = User::find($request->input('id'));
        if (!$user) {
            return response()->json(array('result' => false,'error' => "No user found"), 500);
        }

        //update user with plain text pins and passwords
        try{
            if($input['pin']){
                $input['pin'] = Hash::make($input['pin'],array('rounds'=>4));
            }
            if($input['password']){
                $input['password'] = Hash::make($input['password'],array('rounds'=>4));
            }

            $user->update($input);
            return response()->json(array('result' => true), 200);
        } catch (\Exception $e) {
            return response()->json(array('result' => false,'error' => $e->getMessage()), 500);
        }
    }

    public function bulkStore(Request $request)
    {
        $updated=0;
        $notUpdated=0;
        try {
            $data = $request->all();
            foreach ($data as $user) {
                $userCheck = User::find($user['id']);
                if ($userCheck) {
                    $userCheck->update($user);
                    ++$updated;
                }else{
                    ++$notUpdated;
                }
            }
            return response()->json(array('result' => true, 'updated' => $updated,'notUpdated'=>$notUpdated), 200);
        }catch(\Exception $e){
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

    /**
     * Display users by role.
     * @param role ID Number
     * @return JSON Object Users in role in DB
     */
    public function getByRole($id)
    {
        $users = User::orderBy('FirstName', 'asc')->orderBy('LastName', 'asc')->where('roles_id', $id)->where('active', 1)->get()->toArray();
        return $users;
    }

      /**
     * Display users by group.
     * @param group ID Number
     * @return JSON Object Users in group in DB
     */
    public function getByGroup($id)
    {
        $usersArray = UserGroup::select('users_id')->where('groups_id', $id)->get('users_id')->toArray();
        $users = User::orderBy('FirstName', 'asc')->orderBy('LastName', 'asc')->whereIn('id', $usersArray)->where('active', 1)->get()->toArray();
        return $users;
    }

    /**
     * @deprecated use upgradeAccessLevel()
     */
    public function changeAccessLevel($groupID,$user,$testType,$supervisor){
        $testTypeID = TestType::select('id')->where('name',$testType)->first('id');
        if($groupID <= 5){
            try {
                UserGroup::where(['users_id' => $user['id'], 'test_id' => $testTypeID['id']])->update(['groups_id' => $groupID, 'updated_by'=>$supervisor]);//,'groups_id'=>$groupID,
                return response()->json(array('result' => true), 200);
            } catch (\Exception $e) {
                return response()->json(array('result' => false, 'error' => $e), 500);
            }
        }else{
            return response()->json(array('result' => false), 406);
        }
    }

    /*
      curl -X POST \
          http://localhost:8080/app/users/upgradeAccessLevel \
          -H 'cache-control: no-cache' \
          -H 'content-type: application/json' \
          -H 'postman-token: 035003dc-7778-9be6-db8f-3fe44939d37f' \
          -d '{
            "test_type": "NetlabTestType:NetlabCropType:Kiwifruit:Fresh Weight",
            "group_id": 4,
            "user_id": 1,
            "supervisor_id": 1,
            "comments": [
                {
                    "questionIndex": 1,
                    "question": "What is the minimum allowable fruit weight?",
                    "comment": "minimum is 76"
                }
            ]
        }'
    */
    public function upgradeAccessLevel(Request $request){
        $testType = $request->get('test_type');
        $groupID = $request->get('group_id');
        $userID = $request->get('user_id');
        $supervisor = $request->get('supervisor_id');
        $comments = $request->get('comments');

        $testTypeID = TestType::select('id')->where('name', $testType )->first('id');

        if($groupID <= 5){
            try {
                UserGroup::where(['users_id' => $userID, 'test_id' => $testTypeID['id']])->update(
                        [
                            'groups_id' => $groupID, 
                            'updated_by'=>$supervisor
                        ]);

                // always insert upgrade (keep record of each one)
                $userGroupComment = [];
                $userGroupComment['users_id'] =  $userID;
                $userGroupComment['test_id'] =  $testTypeID['id'];
                $userGroupComment['groups_id'] =  $groupID;
                $userGroupComment['updated_by'] =  $supervisor;
                $userGroupComment['comments'] =  json_encode($comments);
                UserGroupComment::create($userGroupComment);

                return response()->json(array('result' => true), 200);
            } catch (\Exception $e) {
                return response()->json(array('result' => false, 'error' => $e), 500);
            }
        }else{
            return response()->json(array('result' => false), 406);
        }
    }

    /**
     * Samples will draw from 2 sources. sampler service ESB will be form legacy. Local will be for non-company users.
     * Display users by group.
     * @param group ID Number
     * @return JSON Object Users in group in DB
     */
    public function getSamplers()
    {
        if( ConfigurationController::getLocalLab() == 'EFT'){
            $PackhouseID = 'EFK';
        }else{
            $PackhouseID = ConfigurationController::getLocalLab();
        }
        $ESB = ConfigurationController::getServiceBusSettings();
        $client = new GuzzleHttp\Client([
            'base_uri' => $ESB['address'].':'.$ESB['ports']['SamplerPort'].'/Samplers?pack_id='.$PackhouseID,
            'verify' => false
        ]);
        $res = $client->request('GET');
        return $res; // { "type": "User", ....
    }

    /**
     * Returns the group number for the user for a test.
     * @param UserIDs
     * @param UserID TestName
     * @return JSON Object Users in group in DB
     */
    public function getUserCompetency($user, $test)
    {
        $testTypeID = TestType::select('id')->where('name',$test)->first();
        $group = UserGroup::select('groups_id')->where(['users_id'=>(int)$user['id'], 'test_id'=>$testTypeID['id']])->first();
        return response()->json(array('result' => $group['groups_id']), 200);
    }

    /**
     * Display users by group.
     * @param testType
     * @param group ID Number
     * @return JSON Object Users in group in DB
     */
    public function getByTestGroup($testType,$id)
    {
        $testTypeID = TestType::select('id')->where('name',$testType)->first();
        $usersArray = UserGroup::select('users_id')->where('test_id',$testTypeID['id'])->where('groups_id','>=',$id)->get('users_id')->toArray();
        $users = User::orderBy('FirstName', 'asc')->orderBy('LastName', 'asc')->whereIn('id', $usersArray)->where('active', 1)->get()->toArray();
        return $users;
    }


    /**
     * Display users by test associated group.
     * @param UserId
     * @param testName ML2- testType _id
     * @param group ID Number
     * @return boolean
     */
    public function isUserInGroup($groupId, $userId, $testName = null)//
    {
        $groupId = explode(",", str_replace(array('[',']'), '', $groupId));
        if ($testName != null) {
            $testTypeID = TestType::select('id')->where('name', $testName)->value('id');
        } else {
            $testTypeID = 0;
        }
        if ($groupId != null) {
            foreach ($groupId as $id) {
                $user = UserGroup::where(['groups_id' => $id, 'users_id' => $userId, 'test_id' => $testTypeID])->first();

               if($user != null){
                   return response()->json(array('result' => (bool)true), 200);
               }
               if($id == 1 && $user == null){
                   $recCheck = $this->isUserInGroups("[1,2,3,4]",$userId,$testTypeID);
                   if($recCheck == false){
                       return response()->json(array('result' => (bool)true), 200);
                   }
               }
            }

            return response()->json(array('result' => (bool)false), 200);
        } else {
            return response()->json("Please provide required parameters", 400);
        }
    }

    /**
     * Get user from pin. Has been developed on the
     * premise that pin numbers are to be unique
     * across the board.
     *
     * @param Unique User Pin
     * @return JSON Object User from pin
     */
    public function getByPin($pin)
    {
        $users = User::all()->toArray();
        foreach ($users as $user) {
            if (Hash::check($pin, $user['pin'])) {
                return $user;
            }
        }
        return response()->json(['error' => 'Please provide correct SUPERVISOR pin'], 401);
    }

    /**
     * Display users by role.
     *
     * @return JSON Object Users in role in DB
     */
    public function getBareUsers()
    {
        $users = User::orderBy('FirstName', 'asc')
            ->orderBy('LastName', 'asc')
            ->select('firstName', 'lastName', 'id', 'administrator', 'admin_enabled')
            ->where('active', 1)
            ->get()
            ->filter(function($user){
                return !($user['administrator'] == 1 && $user['admin_enabled'] == 1);
            })
            ->values();

        return $users;
    }

    /**
     * Display users by test associated group.
     * Used for checking if a user has any access record for that given testTypeId
     * @param UserId
     * @param testName NL2- testType _id
     * @param group ID Number
     * @return boolean
     */
    public function isUserInGroups($groupId, $userId, $testID)//
    {
        $hasOne = false;
        $groupId = explode(",", str_replace(array('[',']'), '', $groupId));
        if ($groupId != null) {
            foreach ($groupId as $id) {
                $user = UserGroup::where(['groups_id' => $id, 'users_id' => $userId, 'test_id' => $testID])->first();
                if($user != null){
                    $hasOne = true;
                }
            }
        }
        return $hasOne;
    }

}
