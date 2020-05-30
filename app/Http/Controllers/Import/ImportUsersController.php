<?php
namespace App\Http\Controllers\Import;

use Illuminate\Foundation\Application as app;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Base\ConfigurationController;
use GuzzleHttp\Client;
use App\User;
use Illuminate\Filesystem\Filesystem as file;
use Log;
use Carbon\Carbon;

class ImportUsersController extends Controller
{

    private $SupportUsers;
    private $SupportUsersIDs;
    private $ESB;
    private $Lab;
    private $connection;

    public function __construct()
    {
        //Matching against known legacy staff id's
        // = ["3475","2118","3325","3136","2405","3153","2915","2455","4722","8763","3290"];
        $this->SupportUsersIDs = [];

        if( !app()->environment('local') ){
            $filename = "/opt/laravel/susers.txt";
            $content = file($filename);
            foreach ($content as $u) {
                array_push($this->SupportUsersIDs, trim($u));
            }
        }else{
            Log::info("LOCAL environment. Using hardcoded SupportUsersIDs");
            $this->SupportUsersIDs = ["3475","2118","3325","3136","2405","3153","2915","2455","4722","8763","3290"];
        }


        //$this->SupportUsersIDs = ConfigurationController::getSupportUsers();
        //dd($this->SupportUsersIDs);

    }

    /********************************************
     *  Not defined in construct, for some reason
     * the config does not load in time
     *******************************************/
    private function defineConnections(){
        // Define connections to be used
        $this->connection = ConfigurationController::checkDBConnection();
        $this->ESB = ConfigurationController::getServiceBusSettings();
        $this->Lab = ConfigurationController::getLocalLab();
    }

    /***************************************************
     * IMPORT SERVICE USED TO IMPORT ALL STAFF MANUALLY
     * ENDPOINT: {HOST}/APP/UTEST
     * LARAVEL LOGS FOR ERRORS
     **************************************************/
    public function userImportEndpoint()
    {
        try {
            $this->defineConnections();
            $input = $this->callToESB($this->Lab, $this->ESB);
            $usersArray = json_decode($input);
            $this->processRegularUsers($usersArray);
            return "true";
        }catch(\Exception $e){
            return "false";
        }
    }

    /***************************************************
     * IMPORT SERVICE USED TO IMPORT ALL STAFF MANUALLY
     * ENDPOINT: {HOST}/APP/UTEST
     * LARAVEL LOGS FOR ERRORS
     **************************************************/
    public function legacyUserImport()
    {
        $this->defineConnections();
        $input = $this->callToESB($this->Lab,$this->ESB);
        $usersArray = json_decode($input);
        $this->processRegularUsers($usersArray);
    }

    /***************************************************
     * IMPORT SERVICE USED TO IMPORT SUPPORT STAFF MANUALLY
     * ENDPOINT: {HOST}/APP/UTEST
     * LARAVEL LOGS FOR ERRORS
     **************************************************/
    public function AGF_SUPPORT_IMPORT()
    {
        $this->defineConnections();
        $input = $this->callToESB("EFK", $this->ESB);
        $usersArray = json_decode($input);

        print_r($this->ESB);
        print_r($input);

        // ESB does not seem to be setup anymore. comment for dev purpose
        try{
            $this->processSupportUsers($usersArray);
        }catch(\Exception $e){
            Log::error("Error in Import/AGF_SUPPORT_IMPORT: " . $e);
            Log::error("create test user ");

            $user = new \stdClass; 
            $user->FirstName = 'John';
            $user->LastName = 'Smith';
            $user->address = 'Tetley Road, Katikati';
            $user->Landline = '0725454645';
            $user->MobilePhone = '025454645';
            $user->StaffCode = 'JS';
            $user->StaffID = 1;
            $user->Pin = '0000';
            User::storeImported($user, true);
        }
    }

    /************************************************
     * MAIN CALL TO SERVICE BUS
     * @param $Lab string 3 letter Lab Code
     * @param $ESB  array Service bus Settings
     * @return string JSON
     ***********************************************/
    public function callToESB ($Lab,$ESB){
        try {
            $client = new Client();
            $res = $client->request('GET', $ESB['address'] . ':' . $ESB['ports']['StaffPort'] . '/staff', [
                'connect_timeout' => 5,
                'read_timeout' => 60,
                'timeout' => 120, // Response timeout
                'verify' => false,
                'headers' => [
                    'staff.action' => "Import",
                    'staff.companyCode' => $Lab
                ]
            ]);
            $usersList = $res->getBody()->getContents();
            Log::info("IMPORT::Success in retrieving user data from ESB: " . (string)Carbon::now());
            Log::info("IMPORT::Response from ESB: " . $res->getStatusCode());
            return $usersList;
        } catch (\Exception $e) {
            Log::error("Error in Import/UsersController: " . $e);
            print_r($e->getMessage());
        }
    }

    /*****************************************************
     * GET THE STAFF ID FROM THE LEGACY STAFF ARRAY
     * @param $userArray
     * @return array of Staff Id's
     *****************************************************/
    public function processRegularUsers ($userArray){
        //GET LIST OF STAFF ID's FROM PAYLOAD
        $staffID_Array = $this->extractLegacyStaffID($userArray);
        //DEACTIVATE ANYONE NOT COMING THROUGH
        User::deactivateImported($staffID_Array);

        // UPDATE AND SHORTEN INCOMING LIST
        $localUsers =  User::whereIn('legacy_staff_id',$staffID_Array)->get()->toArray();
        Log::info("Processing Incoming:: ");
        foreach ($userArray as $key1=>$user) { // loop over incoming users
            foreach ($localUsers as $key2=>$luser){ // loop over existing users
                if($user->StaffID == $luser['legacy_staff_id']){
                    User::updateImported($user);
                    unset($userArray[$key1]);
                    unset($localUsers[$key2]);
                }
            }
        }

        // SAVE WHAT IS LEFT OVER AS NEW USERS
        foreach ($userArray as $user) {
            if($user->Pin != NULL){
                User::storeImported($user);
            }else{
                Log::warning($user->FirstName." ".$user->LastName." ".$user->StaffCode." ".$user->StaffID." Has no pin number stored in legacy !" );
            }
        }
    }

    /******************************************************
     * GET THE STAFF ID FROM THE LEGACY STAFF ARRAY
     * @param $userArray
     * @return array of Staff Id's
     ******************************************************/
    public function processSupportUsers ($userArray){
        //TODO: needs to deactivate what users are not comming through for support. Works much like processRegUsers
        // dd($this->SupportUsersIDs);
        User::deactivateSupportImported($this->SupportUsersIDs);
        //IMPORTING SUPPORT USERS FOR NON ERUOFINS ONLY
        if($this->Lab != "EFK" || $this->Lab != "EFT") {
            foreach ($userArray as $user) {
                if (in_array($user->StaffID, $this->SupportUsersIDs)) {
                    Log::info("Processing SUPPORT USER:: " . $user->FirstName . $user->LastName . $user->StaffCode);
                    $localUser = User::where(['firstName' => $user->FirstName, 'lastName' => $user->LastName, 'legacy_staff_type_id' => $user->StaffCode])->first();
                    if ($localUser) {
                        User::updateImported($user);
                    } else {
                        User::storeImported($user, true);
                    }
                }
            }
        }
    }

    /******************************************************
     * HELPER - GET THE STAFF ID FROM THE LEGACY STAFF ARRAY
     * @param $userArray
     * @return array of Staff Id's
     *****************************************************/
    public function extractLegacyStaffID ($userArray){
        $staffID_Array = [];
        foreach ($userArray as $user) {
            array_push($staffID_Array,$user->StaffID);
        }
        return $staffID_Array;
    }

    /*****************************************************
     * CLOCKOUT HOUSEKEEPING USED ONLY FOR AGF
     * @param $staffID
     * @return \Illuminate\Http\JsonResponse
     ****************************************************/
    public function ClockOutHousekeeping($staffID)
    {
        $ESB = ConfigurationController::getServiceBusSettings();
        $client = new Client();
        $res = $client->request('GET', $ESB['address'] . ':' . $ESB['ports']['StaffPort'] . '/staff', [
            'query' => [
                'staffID' => $staffID
            ],
            'headers' => [
                'staff.action' => "NL2_ClockIn",
                'staff.companyCode' => "EFK" //ClockOut is for AGF Staff ONLY but uses generic function hence the hardcode.
            ]
        ]);

        $complete = $res->getBody()->getContents();
        if ($complete === 1) {
            return response()->json(array('result' => (bool)true), 200);
        } else {
            return response()->json(array('result' => (bool)false), 200);
        }
    }

}
