<?php

namespace App\Http\Controllers\PHPSite;

use App\Http\Controllers\Base\ConfigurationController;
use App\Http\Controllers\Controller;
use App\Group;
use App\TestType;
use App\UserGroup;
use App\Http\Controllers\Base\SagController;
use App\Helpers;
use App\User;
use Hash;


class SingleUserActionController extends Controller
{

    /*************************************
     * Pre-View Route Controller :
     * This gets loaded and ran before the view
     * based on controller
     *************************************/

    public function __construct()
    {
        //$this->middleware('jwt.auth', ['except' => ['index']]);
    }

    public function index(){
        return view('site.single.singleDashboard');
    }

    public function addUser(){
        $availableGroups = Group::all();

        return view('site.single.addUsers', [
            'availableGroups' => $availableGroups
        ]);
    }

    public function editUserDashboard($layout){
        $users = User::orderBy('FirstName', 'asc')->orderBy('LastName', 'asc')->get();
        return view('site.single.userEditEntry', [
            'users' => $users,
            'layout'=>$layout
        ]);
    }

    public function editUser($user){
        $availableGroups = Group::all()->toArray();
        $usersGroups = UserGroup::select('groups_id')->where('users_id', $user->id)->get()->toArray();
        $usersGroupsNames = Group::whereIn('id', $usersGroups);
        $userGroupIDs = $usersGroupsNames->get()->pluck('id')->all();
        $isSampler = 0;
        $samplerGroupId = "";
        $isLabTech = 0;
        $labTechGroupId = "";

        foreach($availableGroups as $groups){
            if($groups['group'] === "Sampler"){
                $samplerGroupId = $groups['id'];
            }
            if($groups['group'] === "LabTech"){
                $labTechGroupId = $groups['id'];
            }
        }

        foreach($usersGroups as $usG){
            if($usG['groups_id'] === $samplerGroupId){
                $isSampler = 1;
            }elseif($usG['groups_id'] === $labTechGroupId){
                $isLabTech = 1;
            }
        }

        $pswReset = false;
        return view('site.single.userEdit', [
            'user' => $user,
            'availGroups' => $availableGroups,
            'usersGroups' => $usersGroupsNames->get()->toArray(),
            'userGroupIDs' => $userGroupIDs,
            'pswReset' => $pswReset,
            'isSampler' => $isSampler,
            'isLabTech'=>$isLabTech,
            'samplerGroupId' => $samplerGroupId,
            'labTechGroupId' => $labTechGroupId
        ]);
    }

    public function editUserCutters($user){
        $availableGroups = Group::where('group_type',"Cutter")->get()->toArray();
        $userAllGroups = UserGroup::select('groups_id', 'test_id')->where('users_id', $user->id)->get()->toArray();
        $usersGroups = UserGroup::select('groups_id')->where('users_id', $user->id)->get()->toArray();
        $usersGroupsNames = Group::whereIn('id', $usersGroups);//
        $userGroupIDs = $usersGroupsNames->get()->pluck('id')->all();//
        $checked = false;
        return view('site.single.editCutters', [
            'user' => $user,
            'availGroups' => $availableGroups,
            'usersGroups' => $usersGroupsNames->get()->toArray(),
            'checked' => $checked,
            'fullUserGroups' => $userAllGroups,
            'userGroupIDs' => $userGroupIDs
        ]);
    }

    public function editUserFunctions($user){
        $availableGroups = Group::orderby('group','asc')->where('group_type',"Calibration" )->orWhere('group_type',"Operator")->orWhere('group_type',"Report")->get()->toArray();
        $userAllGroups = UserGroup::select('groups_id', 'test_id','updated_at','updated_by')->where('users_id', $user->id)->get()->toArray();
        $usersGroups = UserGroup::select('groups_id')->where('users_id', $user->id)->get()->toArray();
        $usersGroupsNames = Group::whereIn('id', $usersGroups);//
        $userGroupIDs = $usersGroupsNames->get()->pluck('id')->all();//
        $checked = false;
        $filled = false;

        return view('site.single.editUserFunctions', [
            'user' => $user,
            'availGroups' => $availableGroups,
            'usersGroups' => $usersGroupsNames->get()->toArray(),
            'checked' => $checked,
            'filled'=>$filled,
            'fullUserGroups' => $userAllGroups,
            'userGroupIDs' => $userGroupIDs
        ]);
    }

    /**
     *
     * @param $searchKeys mixed
     * Keys that are used to search the couch documents
     * @return array Errors
     */
    public function getSuperCommentsArea($searchKeys)
    {
        $sagObj = SagController::getSagObj();
        $res = $sagObj->get("_design/NetlabSupervisorComment/_view/area?include_docs=true&keys=".urlencode("[\"".$searchKeys."\"]"));
        $res = (array)$res->body->rows;
        $array = [];
        foreach($res as $index){
            array_push($array,(array)$index->doc);
        }
        return $array;
    }

    /**
     * @param  $user The user object of the user you wish to return errors for
     * @param  $params Key
     * @return Array
     */

    public function getUserErrors($user, $params)
    {
        $userErrorsArray = [];
        //Why are we trying to get all errors?
        /*
        $allErrorsArray = $this->getSuperCommentsArea($params);

        foreach ($allErrorsArray as $index) {
            if ($index['operator'] == $user['id']) {
                $index['appliance_test'] = TestType::where("name", $this->getTestInfo($index["test"]))->first();
                array_push($userErrorsArray, $index);
            }
    }*/

        //get errors for user
//dd($errors);
//get available options for groups and tests
        $availableGroups = Group::all()->toArray();
        $testTypes = TestType::all()->toArray();

//get those groups associated with this particular user and
//pass them as and array of numbers
        $userAllGroups = UserGroup::select('groups_id', 'test_id','updated_at','updated_by')->where('users_id', $user->id)->get()->toArray();
        $usersGroups = UserGroup::select('groups_id')->where('users_id', $user->id)->get()->toArray();
        $usersGroupsNames = Group::whereIn('id', $usersGroups);//
        $userGroupIDs = $usersGroupsNames->get()->pluck('id')->all();//
        $avail_crops = [];
        foreach(ConfigurationController::getCurrentCropSeason() as $key=>$value){
            array_push($avail_crops,$key);
        }

//get those tests based on groups from this particular user
        $usersTests = UserGroup::select('test_id')->where('users_id', $user->id)->get()->toArray();
        $usersTestsNames = TestType::whereIn('id', $usersTests);//
        $userTestIDs = $usersTestsNames->get()->pluck('id')->all();//
//dd($userGroupIDs,$userTestIDs,$usersGroups,$userAllGroups);
        $checked = false;
        return view('site.single.editTestGroups', [
            'user' => $user,
            'availGroups' => $availableGroups,
            'usersGroups' => $usersGroupsNames->get()->toArray(),
            'testTypes' => $testTypes,
            'usersTests' => $userTestIDs,
            'checked' => $checked,
            'fullUserGroups' => $userAllGroups,
            'userGroupIDs' => $userGroupIDs,
            'crops' => $avail_crops,
            'errors' => $userErrorsArray,
            'selected_crop' => ""
        ]);


        //return $userErrorsArray;
    }

    public function getTestInfo($testString)
    {
        $testdbString = "";
        $stringArray = explode(":", $testString);
        foreach ($stringArray as $index) {
            if ($index === "NetlabTestType") {
                $testIndex = array_search($index, $stringArray);
                $testdbString = $stringArray[$testIndex] . ":" . $stringArray[$testIndex + 1] . ":" . $stringArray[$testIndex + 2] . ":" . $stringArray[$testIndex + 3];
            }
        }
        return $testdbString;
    }
}
