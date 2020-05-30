<?php

namespace App\Http\Controllers\Base;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Log;
use DB;

class ConfigurationController extends Controller
{

    public function __construct(){
       // $this->middleware('jwt.auth', ['except' => ['index']]);

    }

    /**
     * @return array Load The config file from FS
     */
    public static function getConfig(){
        $source = $_SERVER['DOCUMENT_ROOT'];
        $source = str_replace("public","",$source);
        $confExists = file_exists($source."CONFIG.json");
        if($confExists) {
            $contents = file_get_contents($source."CONFIG.json");
            $contents = json_decode($contents, true);
            //dd($contents);
            return $contents['Configuration'];
        }else{

            return array("error"=>"Error: Please make sure you have your NL2 config file");
        }
    }

    public static function getLocalLab(){
        $config = ConfigurationController::getConfig();
        return $config['Lab'];

    }

    public static function getSupportUsers(){
        $config = ConfigurationController::getConfig();
        return isset($config['SUser']) ? $config['SUser'] : null;
    }

    public static function getLocalPackhouse(){
        $config = ConfigurationController::getConfig();
        return $config['Packhouse'];
    }

    public static function getCouchSettings(){
        $config = ConfigurationController::getConfig();
        $couchSettings = array('address'=>$config['CouchIP'],'port'=>$config['CouchPort'],'database'=>$config['Database']);
        return $couchSettings;
    }

    public static function getServiceBusSettings(){
        $config = ConfigurationController::getConfig();
        $ports = [];
        foreach($config as $key => $value){
            if((strpos($key, 'Port') !== false)&& $key !== "CouchPort" && $key !== "RabbitPort"){
                $ports[$key] = $value;
            }
        }
        $serviceBusSettings = array('address'=>$config['ESB'],'ports'=>$ports);
        return $serviceBusSettings;
    }

    public static function getMessageServiceSettings(){
        $config = ConfigurationController::getConfig();
        $messageServiceSettings = array('address'=>$config['Rabbit'],'port'=>$config['RabbitPort']);
        return $messageServiceSettings;
    }

    public static function getCurrentCropSeason(){
        $config = ConfigurationController::getConfig();
        $cropSeasonSettings = $config['CropSeason'];
        return $cropSeasonSettings;
    }

    public function getServerTime(){
        return json_encode( Carbon::now());
    }

    public static function checkDBConnection(){
        // Test database connection
        try {
            DB::connection()->getPdo();
            return true;
        } catch (\Exception $e) {
            Log::error("Can not connect to mysql DB - Check .env");
            Log::error($e->getMessage());
           return false;
        }
    }

}


//TODO: Look into deployment methods. research using git.
/* I believe I was trying to use this to automatically update the versions of the appliances when a new pull request was made to development.
    public function update()
    {
        /**
         * GIT DEPLOYMENT SCRIPT
         *
         * Used for automatically deploying websites via GitHub
         *


        // array of commands
        $commands = array(
            'echo $PWD',
            'whoami',
            'git status',
            'git submodule sync',
            'git submodule update',
            'git submodule status',
        );

        // exec commands
        $output = '';
        foreach($commands AS $command){
            $tmp = shell_exec($command);

            echo "<span style=\"color: #6BE234;\">\$</span><span style=\"color: #729FCF;\">{$command}\n</span><br />";
            echo htmlentities(trim($tmp)) . "\n<br /><br />";
            //$output .= "<span style=\"color: #6BE234;\">\$</span><span style=\"color: #729FCF;\">{$command}\n</span><br />";
            //$output .= htmlentities(trim($tmp)) . "\n<br /><br />";
        }

        return $output;
    }

/**
     * Display the specified resource.
     *
     * @return \Illuminate\Http\Response

public function getFromConfig()
{
    try {
        $lab = $this->dbo->get(urlencode("NetlabLab:".$this->labO));
        $labData = json_decode(json_encode($lab->body),true);
    } catch (Exception $e) {
        die("Unable to get document : " . $e->getMessage());
    }
    return $labData;
}
    */
