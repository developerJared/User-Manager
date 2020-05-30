<?php

namespace App\Http\Controllers\PHPSite;

use App\Http\Controllers\Base\ConfigurationController;
use App\Http\Controllers\Controller;
use App\Group;
use App\TestType;
use App\UserGroup;
use App\Http\Requests;
use App\Helpers;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Redirect;
use App\User;
use Hash;

class MultiUserActionController extends Controller
{

    public function __construct()
    {
        //$this->middleware('jwt.auth', ['except' => ['index']]);
    }

    public function index()
    {
        return view('site.all.allDashboard');
    }

    public function testAccess(){

        $availableGroups = Group::all()->toArray();
        $testTypes = TestType::all()->toArray();
        $avail_crops = [];
        foreach(ConfigurationController::getCurrentCropSeason() as $key=>$value){
            array_push($avail_crops,$key);
        }
        $users = User::orderBy('FirstName', 'asc')->orderBy('LastName', 'asc')->where(['active'=>1])->get()->toArray();
        $checked = false;
        $ttID = 1;
        return view('site.all.testAccess',[
            'users'=>$users,
            'availGroups'=>$availableGroups,
            'testTypes' => $testTypes,
            'crops' => $avail_crops,
            'checked' => $checked,
            'ttID'=>$ttID,
            'selected_crop'=>""
        ]);

    }

 public function functionsAccess(){
     $availableFunctions = Group::orderby('group','asc')->where('group_type',"Calibration" )->orWhere('group_type',"Operator")->orWhere('group_type',"Report")->get()->toArray();
     $groups_array = [];
     foreach ($availableFunctions as $group){
         array_push($groups_array, $group['id']);
     }
     $users = User::orderBy('FirstName', 'asc')->orderBy('LastName', 'asc')->where(['active'=>1])->get()->toArray();
     $user_groups = UserGroup::whereIn('groups_id',$groups_array)->get()->toArray();
     $checked = false;
     $counter = 1;
     return view('site.all.functionsAccess',[
         'active_groups'=>$user_groups,
         'users'=>$users,
         'functions' => $availableFunctions,
         'checked' => $checked,
         'counter' => $counter
     ]);
 }


}