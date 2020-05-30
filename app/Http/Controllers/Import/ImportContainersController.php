<?php

namespace App\Http\Controllers\Import;

use App\Container;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Base\ConfigurationController;
use GuzzleHttp\Client;
use Monolog;
use Carbon\Carbon;
use Illuminate\Http\Request;
use DB;
use Symfony\Component\Debug\ExceptionHandler;

class ImportContainersController extends Controller
{
    private $log;
    public function __construct(){
        $this->log = new Monolog\Logger(__METHOD__);
        $this->log->pushHandler(new Monolog\Handler\StreamHandler(storage_path().'/logs/BoatImport.log'));

    }

    /***************************************************
     * IMPORT SERVICE USED TO IMPORT ALL CONTAINERS
     * LARAVEL LOGS FOR ERRORS
     * @param $containerType
     * @return JSON
     * Working... need to make transport say all boast not just <5.5 and nulls for NON EFK-EFT
     **************************************************/
    public function boatsImport($lastBoatId = null)
    {
        if($lastBoatId == null) {
            $this->log->addInfo('B Import INIT: ' . (string)Carbon::now());
        }else{
            $this->log->addInfo('B Import subsequent');
        }
        $ESB = ConfigurationController::getServiceBusSettings();
        $client = new Client();
        try {
            if($lastBoatId){
                /*******************
                 * Subsequent Query
                 ******************/
                $res = $client->request('GET','IP' . ':' . $ESB['ports']['ContainerPort'] . '/containers', [
                    'query' => [
                        'containerType' => 'Boat',
                        'where'=> 'BoatId > '.$lastBoatId.' Limit 50000'
                    ]
                ]); //$ESB['address']
            }else{
                /****************
                 * Initial Query
                 ***************/
                $res = $client->request('GET','IP' . ':' . $ESB['ports']['ContainerPort'] . '/containers', [
                    'query' => [
                        'containerType' => 'Boat',
                        'where'=> '1=1 Limit 50000'
                    ]
                ]);
            }
            $boats = json_decode($res->getBody()->getContents());
            $this->log->addInfo("Response from ESB: " . $res->getStatusCode());
            if($boats == [] && $lastBoatId != null){
                $this->processBoats($boats,true);
            }else{
                $this->processBoats($boats);
            }
        } catch (\Exception $e) {
            $this->log->addError("Error Importing Boats ImportContainersController: " . $e);
        }
    }

    public function processBoats ($boats,$done=false){
        try {
            if ($boats == [] && $done) {
                $this->log->addInfo("Import:Boats: All boats have been imported.");
                return;
            } else {
                //Process boats for saving
                $this->saveBoat($boats);
                $last = end($boats)->BoatId;
                $this->boatsImport($last);
            }
        }catch(\Exception $e){
            $this->log->addError("Error Processing Boats ImportContainersController: " . $e);
        }
    }

    public function saveBoat ($boats){
        try {
            for ($i = 0; $i < sizeof($boats); $i++) {
                $legacy_boat = $boats[$i];
                $legacy_barcode = str_pad($legacy_boat->Barcode, 6, "0", STR_PAD_LEFT);
                /*DB::statement("INSERT INTO containers (barcode,weight,nl_id,containerType)
                                      values (?,?,?,?) ON DUPLICATE KEY UPDATE `weight` = $legacy_boat->StdBoatWeight ",
                                    array($legacy_barcode,$legacy_boat->StdBoatWeight,"NetlabContainer:NetlabContainerType:Boat:" . $legacy_barcode,"NetlabContainerType:Boat"));*/
                $existingContainer = Container::find($legacy_barcode);
                if($existingContainer){
                    $existingContainer->weight = $legacy_boat->StdBoatWeight;
                    $existingContainer->save();
                }else{
                    $boat = new Container();
                    $boat->barcode = $legacy_barcode;
                    $boat->weight = $legacy_boat->StdBoatWeight;
                    $boat->nl_id = "NetlabContainer:NetlabContainerType:Boat:" . $legacy_barcode;
                    $boat->containerType = "NetlabContainerType:Boat";
                    $boat->save();
                }
            }
        }catch(\Exception $e){
            $this->log->addError("Error Saving Boats ImportContainersController: " . $e);
        }
    }

    public function preloadContainers(Request $request){
        $input = $request->all();

        if(!array_key_exists('type',$input)){
            return response()->json(array('error'=> "Must specify Type: ie. Boat, Tray, Dish."),400);
        }
        if(!array_key_exists('start',$input )){
            return response()->json(array('error'=> "Must have a start value or barcode."), 400);
        }
        if(!array_key_exists('end',$input)){
            return response()->json(array('error'=> "Must have an end value or barcode, in the event of a singular preload, set same as start."), 400);
        }

        try{
            if( isset($input['type'])&&isset($input['start'])&&isset($input['end'])) {
                Container::preload($input['type'], $input['start'], $input['end']);
                return response()->json(array('result' => true), 200);
            }else{
                return response()->json(array('error'=> "Please check the values, all must have a value"), 400);
            }
        }catch(\Exception $e){
            dd($e);
            return response()->json(array('error'=> $e->getMessage()), $e->getCode());
        }

    }


}
