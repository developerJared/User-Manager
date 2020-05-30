// database/seeds/DatabaseSeeder.php

<?php

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use App\User;
use App\Container;
use App\Group;
use App\Role;
use App\RoleGroup;
use App\TestType;
use App\UserGroup;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash as Hash;

use App\Http\Controllers\Base\ConfigurationController as Settings;
use App\Http\Controllers\Import\ImportUsersController as ImportUsers;
use App\Http\Controllers\Import\ImportContainersController as RemoteContainers;

use Carbon\Carbon;
use Symfony\Component\Console\Helper\ProgressBar;
//use Symfony\Component\Console\Output\OutputInterface;


class DatabaseSeeder extends Seeder
{


    public function run()
    {
        $config = Settings::getConfig();
        $userController = new App\Http\Controllers\Import\ImportUsersController;
        // create a new progress bar (50 units)
        $this->command->info('OverAll Progress');
        $progress = new ProgressBar($this->command->getOutput(),6);

        // start and displays the progress bar
        $progress->start();
        $this->createInitUser($config);
        $progress->advance();
        $this->importUsers($config,$userController);
        $progress->advance();
        $this->importGroups();
        $progress->advance();
        $this->importRoles();
        $progress->advance();
        $this->importRoles_Groups();
        $progress->advance();
        $this->importTestTypes();
        $progress->advance();
        $this->importUsersGroups();
        //$progress->advance();
        //$this->importContainers();
        // ensure that the progress bar is at 100%
        $progress->finish();
        $this->command->info("\n");
        $this->command->info(' All Done!');
    }

    function createInitUser($config){

        DB::table('users')->insert([
            'nl_name' => "NetlabUser:Support AGF",
            'firstName' => "Support",
            'lastName' => "AGF",
            'address' => "137 Tetley Rd, Katikati NewZealand",
            'phone' => "+64 7 549 1044",
            'mobile' => NULL,
            'username' => "Support",
            'password' => Hash::make("Sup3rM3ga!",array('rounds'=>4)),
            'pin' =>  Hash::make("111111",array('rounds'=>4)),
            'current_lab' => $config['Lab'],
            'roles_id'=> 5,
            'active'=> 1,
            'created_at' => Carbon::now(), //created_at AND updated_at
            'updated_at' => Carbon::now(),
            'legacy_staff_type_id' => NULL,
            'legacy_user_id' => NULL,
            'legacy_staff_id' => NULL,
        ]);
    }

    function importUsers ($config, ImportUsers $importUsers){
        Model::unguard();

        $importUsers->AGF_SUPPORT_IMPORT();

       /* $users = RemoteUsers::seederImport();
        foreach($users as $user){
            try {
                $saveUser = array();
                $saveUser['password'] = Hash::make("test", array('rounds' => 4));
                $saveUser['nl_name'] = "NetlabUser:" . $user->FirstName . " " . $user->LastName;
                $saveUser['firstName'] = $user->FirstName;
                $saveUser['lastName'] = $user->LastName;
                $saveUser['address'] = $user->address;
                $saveUser['phone'] = $user->Landline;
                $saveUser['mobile'] = $user->MobilePhone;
                $saveUser['username'] = $user->FirstName;
                $saveUser['active'] = 1;
                if ($user->Pin != null) {
                    $saveUser['pin'] = Hash::make($user->Pin, array('rounds' => 4));
                } else {
                    $pin = $this->randomPin($user->FirstName, $user->LastName);
                    $saveUser['pin'] = Hash::make($pin, array('rounds' => 4));
                }
                $saveUser['current_lab'] = $config['Configuration']['Lab'];
                $saveUser['legacy_staff_type_id'] = $user->StaffCode;
                $saveUser['legacy_staff_id'] = $user->StaffID;
                $saveUser['roles_id'] = 5;
                User::create($saveUser);
            }catch(Exception $e){
                print_r($e);
            }
        }*/
        Model::reguard();
    }

    public function randomPin($firstName, $lastName){
        $config = Settings::getConfig();
        $lab = $config['Configuration']['Lab'];
        $packhouse = $config['Configuration']['Packhouse'];
        $digits = 4;
        $pin = str_pad(rand(0, pow(10, $digits)-1), $digits, '0', STR_PAD_LEFT);
        try {
            $pinFile = fopen("/tmp/".$packhouse."_".$lab."_BasePins.txt", "a+");
            fwrite($pinFile, $firstName . " " . $lastName . "  " . $pin . "\n");
            fclose($pinFile);
            return $pin;
        }catch(Exception $e){
            print_r($e);
        }
    }

    function importContainers(){
        Model::unguard();
        $containers = RemoteContainers::seederContainersImport();
        //either loop over containers raw query OR just insert the whole query...
        Container::create(DB::raw('Insert Into'));

        $res = $this->object_to_array($containers);

        foreach($res as $position){
            $res1  = array();
            try {

                Container::create($res1);
            }catch(Exception $e){
                print_r("An error occurred in ImportContainers: $e");
            }
        }
        Model::reguard();
    }

    function object_to_array($data) {
        if(is_array($data) || is_object($data)){
            $result = array();
            foreach($data as $key => $value){
                $result[$key] = $this->object_to_array($value);
            }
            return $result;
        }
        return $data;
    }

    function importGroups(){
        Model::unguard();
        $groups = [
            ["id"=>1 ,"group"=>"NoAccess","group_type"=>"Access"],
            ["id"=>2 ,"group"=>"UnderSupervision","group_type"=>"Access"],
            ["id"=>3 ,"group"=>"Competent","group_type"=>"Access"],
            ["id"=>4 ,"group"=>"Supervisor","group_type"=>"Access"],
            ["id"=>5 ,"group"=>"BulkWeight Operator","group_type"=>"Operator"],
            ["id"=>6 ,"group"=>"Brix Cutter","group_type"=>"Cutter"],
            ["id"=>7 ,"group"=>"Colour Cutter","group_type"=>"Cutter"],
            ["id"=>8 ,"group"=>"Pressure Cutter","group_type"=>"Cutter"],
            ["id"=>9 ,"group"=>"Scales 20gm Check","group_type"=>"Calibration"],
            ["id"=>10,"group"=>"Penotrometer Calibration","group_type"=>"Calibration"],
            ["id"=>11,"group"=>"Chromameter Calibration","group_type"=>"Calibration"],
            ["id"=>14,"group"=>"Lab Samples Report","group_type"=>"Report"],
            ["id"=>15,"group"=>"Dry Matter Approval","group_type"=>"Test"],
            ["id"=>16,"group"=>"Dry Matter Audit","group_type"=>"Operator"],
            ["id"=>17,"group"=>"Drier Management","group_type"=>"Operator"],
            ["id"=>18,"group"=>"Sample Batch","group_type"=>"Operator"],
            ["id"=>19,"group"=>"Hardware","group_type"=>"Operator"],
            ["id"=>20,"group"=>"Configuration","group_type"=>"Operator"],
            ["id"=>21,"group"=>"User Manager","group_type"=>"Operator"],
            ["id"=>22,"group"=>"Validations","group_type"=>"Operator"],
            ["id"=>23,"group"=>"Setup","group_type"=>"Operator"],
            ["id"=>24,"group"=>"Incomplete Tests","group_type"=>"Report"],
            ["id"=>25,"group"=>"Sample Results","group_type"=>"Operator"],
            ["id"=>26,"group"=>"Container Manager","group_type"=>"Operator"],
            ["id"=>28,"group"=>"Control Panel","group_type"=>"Operator"],
          	["id"=>29,"group"=>"Refractometer Calibration","group_type"=>"Calibration"],
        ];
        foreach($groups as $group){
            Group::create($group);
        }
        Model::reguard();
    }

    function importRoles(){
        Model::unguard();
        $roles = [
            1=> ["role"=>"User"],
            2=> ["role"=>"Operator"],
            3=> ["role"=>"Supervisor"],
            4=> ["role"=>"Manager"],
            5=> ["role"=>"Administrator"]
        ];
        foreach($roles as $role){
            Role::create($role);
        }
        Model::reguard();
    }

    function importRoles_Groups(){
        Model::unguard();
        $rolesGroups = [
            1=>  ["roles_id"=>1,"groups_id"=>1],
            2=>  ["roles_id"=>1,"groups_id"=>2],
            3=>  ["roles_id"=>2,"groups_id"=>2],
            4=>  ["roles_id"=>1,"groups_id"=>3],
            5=>  ["roles_id"=>2,"groups_id"=>3],
            6=>  ["roles_id"=>3,"groups_id"=>3],
            7=>  ["roles_id"=>4,"groups_id"=>3],
            8=>  ["roles_id"=>1,"groups_id"=>4],
            9=>  ["roles_id"=>2,"groups_id"=>4],
            10=> ["roles_id"=>3,"groups_id"=>4],
            11=> ["roles_id"=>4,"groups_id"=>4],
            12=> ["roles_id"=>5,"groups_id"=>4],

        ];
        foreach($rolesGroups as $entry){
            RoleGroup::create($entry);
        }
        Model::reguard();
    }

    function importTestTypes(){
        Model::unguard();
        $testTypes = [
            1=> ["name"=>"NetlabTestType:NetlabCropType:Avocado:Bulk Weight 	            ", "shortname"=>"BlkWgt	  ", "longname"=>" Bulk Weight	             ", "crop"=>"  Avocado"],
            2=> ["name"=>"NetlabTestType:NetlabCropType:Avocado:Dry In      	            ", "shortname"=>"DryIn	  ", "longname"=>" Dry In	                 ", "crop"=>"  Avocado"],
            3=> ["name"=>"NetlabTestType:NetlabCropType:Avocado:Dry Out     	            ", "shortname"=>"DryOut	  ", "longname"=>" Dry Out	                 ", "crop"=>"  Avocado"],
            4=> ["name"=>"NetlabTestType:NetlabCropType:Avocado:Fresh Weight	            ", "shortname"=>"FrshWgt  ", "longname"=>"  Fresh Weight	         ", "crop"=>"  Avocado"],
            5=> ["name"=>"NetlabTestType:NetlabCropType:Kiwifruit:Brix Equatorial	        ", "shortname"=>"BrxEq	  ", "longname"=>" Brix Equatorial	         ", "crop"=>"  Kiwifruit"],
            6=> ["name"=>"NetlabTestType:NetlabCropType:Kiwifruit:Brix Stem and Blossom     ", "shortname"=>"BrxSt&Bs ", "longname"=>"  Brix Stem and Blossom	 ", "crop"=>"  Kiwifruit"],
            7=> ["name"=>"NetlabTestType:NetlabCropType:Kiwifruit:Bulk Weight	            ", "shortname"=>"BlkWgt	  ", "longname"=>" Bulk Weight	             ", "crop"=>"  Kiwifruit"],
            8=> ["name"=>"NetlabTestType:NetlabCropType:Kiwifruit:Colour	                ", "shortname"=>"Colour	  ", "longname"=>" Colour	                 ", "crop"=>"  Kiwifruit"],
            9=> ["name"=>"NetlabTestType:NetlabCropType:Kiwifruit:Colour Double	            ", "shortname"=>"ColDbl	  ", "longname"=>" Colour Double	         ", "crop"=>"  Kiwifruit"],
            10=>["name"=>"NetlabTestType:NetlabCropType:Kiwifruit:Dry In	                ", "shortname"=>"DryIn	  ", "longname"=>" Dry In	                 ", "crop"=>"  Kiwifruit"],
            11=>["name"=>"NetlabTestType:NetlabCropType:Kiwifruit:Dry Out	                ", "shortname"=>"DryOut	  ", "longname"=>" Dry Out	                 ", "crop"=>"  Kiwifruit"],
            12=>["name"=>"NetlabTestType:NetlabCropType:Kiwifruit:External Defects	        ", "shortname"=>"ExtDef	  ", "longname"=>" External Defects	         ", "crop"=>"  Kiwifruit"],
            13=>["name"=>"NetlabTestType:NetlabCropType:Kiwifruit:Fresh Weight              ", "shortname"=>"FrshWgt  ", "longname"=>"  Fresh Weight	         ", "crop"=>"  Kiwifruit"],
            14=>["name"=>"NetlabTestType:NetlabCropType:Kiwifruit:Internal Defects          ", "shortname"=>"IntDef	  ", "longname"=>" Internal Defects	         ", "crop"=>"  Kiwifruit"],
            15=>["name"=>"NetlabTestType:NetlabCropType:Kiwifruit:Pressure                  ", "shortname"=>"Pres	  ", "longname"=>" Pressure	                 ", "crop"=>"  Kiwifruit"],
            16=>["name"=>"NetlabTestType:NetlabCropType:Kiwifruit:Pressure Double           ", "shortname"=>"PresDbl  ", "longname"=>"  Pressure Double	         ", "crop"=>"  Kiwifruit"],
            17=>["name"=>"NetlabTestType:NetlabCropType:Kiwifruit:Seeds                     ", "shortname"=>"Seeds	  ", "longname"=>" Seeds	                 ", "crop"=>"  Kiwifruit"],
            18=>["name"=>"NetlabTestType:NetlabCropType:Kiwifruit:Sample Login              ", "shortname"=>"SampLog  ", "longname"=>"  Sample Login	         ", "crop"=>"  Kiwifruit"],
            19=>["name"=>"NetlabTestType:DryMatterApproval	                                ", "shortname"=>"DryMatAp ", "longname"=>"  DryMatterApproval	     ", "crop"=>"  All"],
            20=>["name"=>"NetlabTestType:NetlabCropType:Avocado:Colour                      ", "shortname"=>"Colour	  ", "longname"=>" Colour	                 ", "crop"=>"  Avocado"],
            21=>["name"=>"NetlabTestType:NetlabCropType:Kiwifruit:RNA                       ", "shortname"=>"RNA	  ", "longname"=>"     RNA	                 ", "crop"=>"  Kiwifruit"],
            22=>["name"=>"NetlabTestType:NetlabCropType:Kiwifruit:Beak Damage               ", "shortname"=>"BekDmg	  ", "longname"=>" Beak Damage	             ", "crop"=>"  Kiwifruit"],
            23=>["name"=>"NetlabTestType:NetlabCropType:Kiwifruit:Beak Assessment           ", "shortname"=>"BekAs	  ", "longname"=>" Beak Assessment	         ", "crop"=>"  Kiwifruit"],
            24=>["name"=>"NetlabTestType:NetlabCropType:Kiwifruit:Storage Breakdown Disorder", "shortname"=>"StoBrkDis", "longname"=>"Storage Breakdown Disorder", "crop"=>"Kiwifruit"],
            25=>["name"=>"NetlabTestType:NetlabCropType:Kiwifruit:TA	                    ", "shortname"=>"TA	      ", "longname"=>" TA	                     ", "crop"=>"  Kiwifruit"],
            26=>["name"=>"NetlabTestType:NetlabCropType:Avocado:Sample Login	            ", "shortname"=>"SampLog  ", "longname"=>"  Sample Login	         ", "crop"=>"  Avocado"]
        ];

        foreach($testTypes as $testType){
            TestType::create($testType);
        }
        Model::reguard();
    }

    function importUsersGroups(){
        $users= User::all('id');
        $testTypes = TestType::all();
        $functionGroups = Group::where('id', '>', 4)->get();
        Model::unguard();
        /******************************************************
         * For each user in DB we want to save a 4 (Supervisor)
         * for each testType initally. Then when we run
         * out of testtypes we need to save user id
         * and what ever groups we have left
         *****************************************************/
        $this->command->info("\n");
        $this->command->info("Assigning Users Privileges...");
        $progress = new ProgressBar($this->command->getOutput(),count($users));

        // start and displays the progress bar
        $progress->start();
        foreach($users as $user) {
            foreach ($testTypes as $testType) {
                $uGroup = [
                    "users_id" => $user->id,
                    "groups_id" => 4,
                    "test_id" => $testType->id,
                    "updated_by" =>"Support AGF"
                ];
                UserGroup::create($uGroup);
            }
            foreach ($functionGroups as $group) {
                $uGroup = [
                    "users_id" => $user->id,
                    "groups_id" => $group->id,
                    "test_id" => 0,
                    "updated_by" =>"Support AGF"
                ];
                UserGroup::create($uGroup);
            }
            $progress->advance();
        }
        $progress->finish();
        $this->command->info("\n");
        Model::reguard();
    }
}
