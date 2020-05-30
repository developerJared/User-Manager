<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Base\ConfigurationController as Config;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\QCBreach;

/**
 * Save events 
 * 
 * Event types: 
 * - QC Breach
 *
 */
class EventsController extends Controller
{
    public function __construct(){
        // $this->middleware('jwt.auth');
    }

    public function index(){

    }

	/**
     * Get events for one sampler
     *
     * @param  string type
     * @param string barcode
     * @return JSON container Object
     */
    public function bySample($sampleNumber)
    {
        $events = QCBreach::bySample($sampleNumber);
        return response()->json($events->toArray(), 200);
    }

    /**
     * Get events for the last 2 days
     *
     * @return JSON container Object
     */
    public function last2days()
    {
        $events = QCBreach::last2days();
        return response()->json($events->toArray(), 200);
    }

    /**
     *
     * @param  Request  $request
     * @return \Illuminate\Http\Response
     */
    public function save(Request $request)
    {
        $input = json_decode($request->getContent(), true);

        // so far, we have only qc_breach event. so we don;t need to look at the event type

        $event = [];
        $event['sample_number'] = $input['sample_number'];
        $event['event'] = $input;
        
        $res = QCBreach::create($event);

        return response()->json(["result" => $res], 200) ;
    }

}