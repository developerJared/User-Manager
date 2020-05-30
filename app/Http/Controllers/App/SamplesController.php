<?php

namespace App\Http\Controllers\App;

use App\Sample;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Base\ConfigurationController;
use App\Http\Controllers\Base\SagController;

class SamplesController extends Controller
{
    private $couch;

    public function __construct(){
        // $this->middleware('jwt.auth');
        $this->couch = SagController::getSagObj();
    }

    /**
     * Store a newly created resource in storage.
     * @param  array
     * @return boolean TRUE for success || error
     */
    public function store($array)
    {
        $sample = [];
        try {
            $sample['sample_number'] = $array['name'];
            $sample['status'] = $array['status'];
            $sample['variety'] = $array['variety'];
            $sample['croptype'] = $array['cropType'];
            $sample['itemcount'] = $array['itemCount'];
            $sample['samplesource'] = $array['sampleSource'];
            $sample['area'] = $array['area'];
            Sample::create($sample);
        }catch(\Exception $e){
            return $e;
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @param  request $request to get an extra flag parameter
     * @return \Illuminate\Http\Response
     */
    public function show($id,Request $request)
    {
        // set flags
        $remote = $request->only(['remote']);
        $conflicts = $request->only(['ignore_conflict']);
        $remote_flag = strtolower($remote['remote']) === 'true'? true: false;
        $ignore_conflict = strtolower($conflicts['ignore_conflict']) === 'true'? true: false;
        //check if sample in couch DB first by trying to retrieve it
        try{
            //check flag
            if($remote_flag){
                return $this->getFromESB($id,$ignore_conflict);
            }

            $sample = $this->getFromCouch($id);
            $doc = json_decode($sample);

            if($doc){
                return response()->json(json_decode($sample), 200) ;
            }else{
                return $this->getFromESB($id);
            }
          //catch any exceptions
        }catch(\SagCouchException $e){
            //if the exception is a not found exception then get the sample from ESB
            if($e->getCode() == 404){
              return $this->getFromESB($id,null);
            }
            return response()->json($e->getMessage(), $e->getCode()) ;
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return String
     */
    public function callToESB($id)
    {
        $ESB = ConfigurationController::getServiceBusSettings();
        $Lab = ConfigurationController::getLocalLab();
        $Season = ConfigurationController::getCurrentCropSeason();
        $client = new Client();
        $res = $client->request('GET',$ESB['address'].':'.$ESB['ports']['SamplePort'].'/netlab/sample', [
            'verify'=>false,
            'query' => ['sample_number' => $id],
            'headers'=>[
                'api.response_schema' => "nl2",
                'assoc.lab' => $Lab, //Off configuration file
                'X-NL2-Season'=>$Season['Kiwifruit'] //season header
            ]
        ]);
        //log to MySql here
        $JsonString = $res->getBody()->getContents();
        //dd($JsonString);
        if($JsonString != null && !empty($JsonString) && $JsonString != "NO SAMPLE FOUND" ){
           // dd($Lab);
            if($Lab == "EFK" || $Lab == "EFT" ){
                $checkVal = json_decode($JsonString);
                $assocLab = $checkVal->samples->legacy->legacy_lab_co;
                $checkArray = [
                    "AGF",
                    "EFK",
                    "EFT",
                    "AGT",
                    "EU",
                    "HIL",
                    "AQ",
                    "VLS"
                ];
                if( in_array($assocLab,$checkArray)){
                    return $JsonString;
                }else{
                    return "NO SAMPLE FOUND";
                }
            }else{
                return $JsonString; //Outside Company
            }
        }else{
            return "NO SAMPLE FOUND";
        }
    }

    public function getFromESB($id,$ignore_conflict=false){
        $remSample = $this->callToESB($id);
        // dd($remSample);
        if($remSample === "NO SAMPLE FOUND"){
            return response()->json("Sample Not Found Anywhere", 404) ;
        }elseif(isset($resArray['errors']['code']) && $resArray['errors']['code'] == 0){
            return response()->json("Sample Not Found Anywhere", 404) ;
        }

        $resArray = json_decode($remSample,true);
        //If an error that is not 404 from ESB return error and code from ESB
        if(isset($resArray['errors']['code']) && $resArray['errors']['code'] != 0){
            return response()->json("Server error", $resArray['errors']['code']);
        }

        // If no error prepend the ID to the array, turn it to object and save it to couchDB
        $sampleArray = $resArray['samples'];
        $sampleArray = array("_id"=>"NetlabSample:".$sampleArray['name']) + $sampleArray;
        //store in couch here
        try{
            $this->saveToCouch(json_encode($sampleArray));

        }catch(\SagCouchException $e){
            if($ignore_conflict){
               return json_encode($sampleArray);
            }else{
                return response()->json($e->getMessage(), $e->getCode()) ;
            }
        }

        //log the saved sample as imported to MySql
        //we want to make sure the sample has been stored
        //successfully in couch before logging it as imported in mysql.
        try {
            $this->store($resArray['samples']);
            // make sure to get the right key in the vase when $id is a uuid
            $id = $sampleArray['name'];
            //finally return the sample object for app consumption
            $sample = $this->getFromCouch($id);
            return response()->json(json_decode($sample), 200) ;
        }catch(\Exception $e){
            return $e;
        }
    }

    public function SeedsProxy($id)
    {
        $ESB = ConfigurationController::getServiceBusSettings();
        $client = new Client();
        $res = $client->request('GET',$ESB['address'].':'.$ESB['ports']['SamplePort'].'/netlab/history/seeds', [
            'query' => ['sample_number' => $id],
        ]);
        //log to MySql here
        return $res->getBody()->getContents();
    }

    /**
     * Save an object to couchDB.
     *
     * @param Sample Object $object
     * @return JSON result from couchdb
     */
    public function saveToCouch($object)
    {
        $sampleCheck = "Sample Not Found Anywhere";
        $sample = json_decode($object);
        try {
            $sampleCheck = $this->checkCouch($sample->name);
        }
        catch(\SagCouchException $e){
            //if the exception is a not found exception then get the sample from ESB
            if($e->getCode() == 404){
                $sampleCheck = "Sample Not Found Anywhere";
            }
        }
        if($sampleCheck == "Sample Not Found Anywhere"){
            $res = $this->couch->post($object);
            $resArray = (array)$res->body;
            $jsonRes = json_encode($resArray);
            return $jsonRes;
        }else{
            $sampleCheck->body->legacy = $sample->legacy;
            $object = json_encode($sampleCheck->body);
            $res = $this->couch->post($object);
            $resArray = (array)$res->body;
            $jsonRes = json_encode($resArray);
            return $jsonRes;
        }
    }

    /**
     * Get and object from couchDB.
     *
     * @param  Sample ID $id
     * @return JSON sample object for ID is status 200
     * else return fault status code
     */
    public function getFromCouch($id = null)
    {
        $res = $this->couch->get('/NetlabSample:' . $id);
        $resArray = (array)$res->body;
        $jsonRes = json_encode($resArray);
        return $jsonRes;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return All CouchDB Samples
     */
    public function getAllFromCouch()
    {
        $res = $this->couch->get("_design/NetlabSample/_view/userAll");
        return $res;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  Sample ID $id
     * @return JSON sample object for ID
     */
    public function checkCouch($id = null)
    {
        $res = $this->couch->get('/NetlabSample:'.$id);
        return $res;
    }


}
