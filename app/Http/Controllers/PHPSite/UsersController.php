<?php

namespace App\Http\Controllers\PHPSite;

use App\Http\Controllers\Base\ConfigurationController;
use App\Http\Controllers\Controller;
use App\UserGroup;
use App\TestType;
use Illuminate\Http\Request;
use App\Http\Requests;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Redirect;
use App\User;
use App\Group;
use Hash;

class UsersController extends Controller
{

    public function __construct()
    {
        //$this->middleware('jwt.auth', ['except' => ['index']]);

    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {

        if ($request->isMethod('post')) {
            return "POST";
        }
        if ($request->isMethod('get')) {

            $token = session('token');
            if ($token != null) {
                return view('site.usersDashboard',array(
                    'Lab'=>ConfigurationController::getLocalLab()
                ));
            } else {
                flash()->error("Please Login Again");
                return redirect('/');
            }
        }
    }

    /*******************************************************************************
     * SINGLE USER SECTION
     ******************************************************************************/

  /*
   * This functionality is not used. Users are created in legacy system ATM Until
   *  Staff management for agfirst is re-written and componentized
   *
   */
    public function createUser(Request $request)
    {
        //need to check session token
        $input = $request->all();
        $userChecks = [
            'firstName'=>$input['firstName'],
            'lastName'=>$input['lastName'],
            'username'=>$input['username'],
        ];
        $checkUser = User::where($userChecks)->first();
        if ($checkUser != null) {
            flash()->error("User Already Exists or Choose a different Username ");
            return redirect('site/addUser');
        }

        try {
            $user = [];
            $user['nl_name'] = "NetlabUser:" . $input['firstName'] . " " . $input['lastName'];
            $user['firstName'] = $input['firstName'];
            $user['lastName'] = $input['lastName'];
            $user['address'] = $input['address'];
            $user['phone'] = $input['phone'];
            $user['mobile'] = $input['mobile'];
            $user['username'] = $input['username'];
            $user['password'] = Hash::make($input['password'],array('rounds'=>4));
            $user['roles_id'] = (int)$input['roles_id'];

            if ((int)$input['roles_id'] == 5) {
                if ($this->checkUniquePIN($input['pin'])) {
                    $user['pin'] = Hash::make($input['pin'],array('rounds'=>4));
                } else {
                    flash()->error("Pin already in use, please choose another");
                    return redirect('site/addUser/')->with("Pin_Error","invalidPin");
                }
            } else {
                $user['pin'] = Hash::make($input['pin'],array('rounds'=>4));
            }
            $user['current_lab'] = $input['current_lab'];

            User::create($user);
            flash()->success("User Created!");
            return redirect('site/users');

        } catch (\Exception $e) {
            return "Error: $e";
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
        $users = User::all()->toArray();
        $available = true;
        foreach ($users as $user) {
            $userPin = $user['pin'];
            if (Hash::check((string)$givenPin, (string)$userPin)) {
                $available = false;

                //dd($user);
            }
        }
        return $available;
    }


    /**
     * Store a new User
     *
     * @param  Request $request : The in coming request from page
     * @return redirect if true
     */
    public function store(Request $request)
    {
        $reqData = $request->all();

        if (array_key_exists('active', $reqData)) {
            $activeBox = $reqData['active'];
        }else{
            $activeBox = 0;
        }

        if (array_key_exists('is_Sampler', $reqData)) {
            $isSampler = $reqData['is_Sampler'];
        }else{
            $isSampler = 0;
        }

        if (array_key_exists('is_LabTech', $reqData)) {
            $isLabTech = $reqData['is_LabTech'];
        }else{
            $isLabTech = 0;
        }

        $samplerGroupId = (int)$reqData['samplerGroupId'];
        $labTechGroupId = (int)$reqData['labTechGroupId'];
        $userID = $reqData['id'];
        $user = [];
        //need to check session token
        try {
            $oldUser = User::where('id',$userID)->first();
            if ($oldUser == null) {
                flash()->error("User not saved");
                return redirect('site/users');
            }
            $user['id'] = $userID;
            $user['nl_name'] = "NetlabUser:" . $reqData['firstName'] . " " . $reqData['lastName'];
            $user['firstName'] = $reqData['firstName'];
            $user['lastName'] = $reqData['lastName'];
            $user['address'] = $reqData['address'];
            $user['phone'] = $reqData['phone'];
            $user['mobile'] = $reqData['mobile'];
            $user['current_lab'] = $reqData['current_lab'];

            if ($reqData['username'] == null) {
                $user['username'] = $oldUser['username'];
            } else {
                $user['username'] = $reqData['username'];
            }

            if ($reqData['password'] == null) {
                $user['password'] = $oldUser['password'];
            } else {
                $user['password'] = Hash::make($reqData['password'],array('rounds'=>4));
            }

            if ($reqData['pin'] != null) { //Unique pin SUPERVISOR ONLY
                if ($this->checkUniquePIN($reqData['pin'])) {
                    $user['pin'] = Hash::make($reqData['pin'],array('rounds'=>4));
                } else {
                    flash()->error("Pin already in use, please choose another")->important();
                    return redirect('site/editUsers/editUser/' . (int)$userID)->with("Pin_Error","invalidPin");
                }
            }
            //elseif ($reqData['pin'] != null) { //Change pin everyone else
            //    $user['pin'] = Hash::make($reqData['pin'],array('rounds'=>4));
            //}
            else {
                $user['pin'] = $oldUser['pin'];
            }

            if ($reqData['roles_id'] == null) {
                $user['roles_id'] = (int)$oldUser['roles_id'];
            } else {
                $user['roles_id'] = (int)$reqData['roles_id'];
            }

            //Check the Active Checkbox
            if ($activeBox != null) {
                $user['active'] = 1;
            } else {
                $user['active'] = 0;
            }

            // check Is Sampler and assign as part of the group or remove from group
            // Need Sampler Group ID to pass into function for adding / removing
            if($isSampler){
                $this->addUserGroup($userID, $samplerGroupId);
            }else{
                $this->removeUserGroup($userID, $samplerGroupId);
            }
            // check Is LabTech and assign as part of the group or remove from group
            // Need LabTech Group ID to pass into function for adding / removing
            if($isLabTech){
                $this->addUserGroup($userID, $labTechGroupId);
            }else{
                $this->removeUserGroup($userID, $labTechGroupId);
            }

            User::where('id',$userID)->update($user);
            flash()->success("User Saved!");

            return redirect('site/editUsers/info');

        } catch (\Exception $e) {
            //might try to flash a message here then redirect to the last page?
            return "Error: $e";
        }
    }

    /*Single User Store Staff Access*/
    public function storeStaffAccess(Request $request)
    {
        $input = $request->all();
        foreach ($input as $key => $value) {
            $loggedin = Session::get('user');
            $loggedinName = $loggedin['firstName']." ".$loggedin['lastName'];
            if (is_numeric($key) && $value != null && !$this->checkUserTestGroup($input['id'], $key)) {
                UserGroup::insert(['users_id' => $input['id'], 'groups_id' => $value, 'test_id' => $key, 'updated_by'=>$loggedinName]);
            } elseif (is_numeric($key) && $value != null && $this->checkUserTestGroup($input['id'], $key)) {
                $ug = UserGroup::where(['users_id' => $input['id'], 'test_id' => $key])->first();
                if ($ug['groups_id'] != $value) {
                    UserGroup::where(['users_id' => $input['id'], 'test_id' => $key])->update(['groups_id' => $value,'updated_by'=>$loggedinName]);
                }
            }
        }
        $this->appendBulkWeightGroup($input['id']);
        flash()->success("User Groups Saved!");
        return redirect('site/editUsers/access');
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

    /*Single User Store Cutters*/
    public function storeCutters(Request $request)
    {
        $input = $request->all();
        /***************************
         *BASE ARRAY FOR FUNCTIONS
         **************************/
        $functions_array = [
            "Brix_Cutter",
            "Colour_Cutter",
            "Pressure_Cutter"
        ];

        /***************************
         *ADDING TO USERS_GROUPS TABLE
         * REMOVING FROM BASE ARRAY
         **************************/
        foreach ($input as $key => $value) {
            if (in_array($key, $functions_array)) {
                try {
                    $this->addUserGroup($input['id'], $value);
                    if (($key = array_search($key, $functions_array)) !== false) {
                        unset($functions_array[$key]);
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
        foreach ($functions_array as $key => $value) {
            if ($functions_array != null && $functions_array != [] && $value != null) {
                $remove_group = Group::where('group', (str_replace("_", " ", $value)))->first();
                try {
                    $this->removeUserGroup($input['id'], $remove_group['id']);
                } catch (\Exception $e) {
                    return $e;
                }
            }
        }

        flash()->success("Cutters Saved!");
        return redirect('site/editUsers/access');
    }

    /*Single User Store Functions*/
    public function storeFunctions(Request $request)
    {
        $input = $request->all();

        /***************************
         *BASE ARRAY FOR FUNCTIONS
         **************************/
        $functions_array = [];
        /**
         *
         *
         * "Calibration_Scales",
         * "Calibration_Penotrometer",
         * "Calibration_Chromameter",
         * "Drier_Management",
         * "Audit_Weight"
         */

        $groupFunctions = Group::getFunctionsArray();
        foreach ($groupFunctions as $key => $value) {
            array_push($functions_array, str_replace(" ", "_", $value['group']));
        }
        /***************************
         *ADDING TO USERS_GROUPS TABLE
         * REMOVING FROM BASE ARRAY
         **************************/
        foreach ($input as $key => $value) {

            if (in_array($key, $functions_array)) {
                try {
                    $this->addUserGroup($input['id'], $value);
                    if (($key = array_search($key, $functions_array)) !== false) {
                        unset($functions_array[$key]);
                    }
                } catch (\Exception $e) {
                    return $e;
                }
            }
        }

        /*******************************************
         *REMOVING FROM USERS_GROUPS TABLE
         * BASED ON WHATS LEFT OF BASE ARRAY
         ******************************************/
        foreach ($functions_array as $key => $value) {
            if ($functions_array != null && $functions_array != [] && $value != null) {
                $remove_group = Group::where('group', (str_replace("_", " ", $value)))->first();
                try {
                    $this->removeUserGroup($input['id'], $remove_group['id']);
                } catch (\Exception $e) {
                    return $e;
                }
            }
        }

        flash()->success("Functions Saved!");
        return redirect('site/editUsers/access');
    }

    /*******************************************************************************
     * MULTIPLE USER SECTION
     ******************************************************************************/

    public function storeTestAccess(Request $request)
    {
        $input = $request->all();
        $testID = null;
        if ($input['available_crops'] == "Kiwifruit") {
            $testID = $input["available_kiwifruit_tests"];
        }
        if ($input['available_crops'] == "Avocado") {
            $testID = $input["available_avo_tests"];
        }


        /* $testID = $input["available_tests"];
         unset($input["available_tests"]);*/

        if (is_numeric($testID)) {
            $loggedin = Session::get('user');
            $loggedinName = $loggedin['firstName']." ".$loggedin['lastName'];
            foreach ($input as $key => $value) {
                if (is_numeric($key) && $value != null && !$this->checkUserTestGroup($key, $testID)) {
                    UserGroup::insert(['users_id' => $key, 'groups_id' => $value, 'test_id' => $testID, 'updated_by' =>$loggedinName]);
                    if ($testID == 1 || $testID == 7) {
                        $this->appendBulkWeightGroup($key);
                    }
                } elseif (is_numeric($key) && $value != null && $this->checkUserTestGroup($key, $testID)) {
                    $ug = UserGroup::where(['users_id' => $key, 'test_id' => $testID])->first();
                    if ($ug['groups_id'] != $value) {
                        UserGroup::where(['users_id' => $key, 'test_id' => $testID])->update(['groups_id' => $value, 'updated_by' =>$loggedinName]);
                    }
                    if ($testID == 1 || $testID == 7) {
                        $this->appendBulkWeightGroup($key);
                    }
                }
            }
        } else {
            //These strings are here to easily tell that this group belongs to Cutters (NOT A TEST)
            //So there is no confusion with numbers on these test_id in database = 0dd($testID);
            switch ($testID) {
                case "Pressure Cutter":
                    $groupID = 6;
                    foreach ($input as $key => $value) {
                        try {
                            $this->addUserGroup($key, $groupID);
                        } catch (\Exception $e) {
                            return $e;
                        }
                    }
                    break;
                case "Brix Cutter":
                    $groupID = 7;
                    foreach ($input as $key => $value) {
                        try {
                            $this->addUserGroup($key, $groupID);
                        } catch (\Exception $e) {
                            return $e;
                        }
                    }
                    break;
                case "Colour Cutter":
                    $groupID = 8;
                    foreach ($input as $key => $value) {
                        try {
                            $this->addUserGroup($key, $groupID);
                        } catch (\Exception $e) {
                            return $e;
                        }
                    }
                    break;
                default:
                    dd("There was an error in Users Controller");//error here
                    break;
            }
        }


        flash()->success("User Groups Saved!");
        return redirect('site/testAccess');

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

        flash()->success("Functions Saved!");
        return redirect('site/users');

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


}
