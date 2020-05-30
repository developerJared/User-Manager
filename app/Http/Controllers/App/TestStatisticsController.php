<?php

namespace App\Http\Controllers\App;

use DB;
use App\TestType;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\TestStatistics;
use Illuminate\Support\Facades\Cache;

class TestStatisticsController extends Controller
{
    public function __construct(){
        // $this->middleware('jwt.auth');
    }

    public function index(){

    }

    /**
    curl -X PUT \
      http://localhost:8080/app/test/stats/setTimes \
      -H 'content-type: application/json' \
      -d '{
        "test_id": "NetlabTestType:NetlabCropType:Kiwifruit:Fresh Weight",
        "sample_number": 999999,
        "legacy_staff_type_id": "AS",
        "tray": 1,
        "tray_size": 15,
        "scan_time": "2019-01-22T20:48:50Z",
        "start_time": "2019-01-22T20:50:50Z",
        "end_time": "2019-01-22T20:54:50Z",
        "sample_date": "2019-01-22"
    }'
     */
    public function setTimes(Request $request){

        $testType = $request->get('test_id');
        $testTypeObj = TestType::select('id')->where('name', $testType )->first('id');

        $record = TestStatistics::where(['sample_number' => $request->get('sample_number'), 'test_id' => $testTypeObj['id'], 'tray' => $request->get('tray')])->first();

        if(!$record){
            $record = new TestStatistics;
        }

        $record->sample_number = $request->get('sample_number');
        $record->tray = $request->get('tray');
        $record->tray_size = $request->get('tray_size');
        $record->operator = $request->get('operator');
        $record->test_id = $testTypeObj['id'];
        $record->sample_date = $request->get('sample_date');
        $record->start_time = $request->get('start_time');
        $record->end_time = $request->get('end_time');
        $record->avg_seconds = $request->get('avg_seconds');
        $record->test_down_time = $request->get('test_down_time');
        $record->scan_time = $request->get('scan_time');

        TestStatistics::updateAverage($record);

        $record->save();

        return response()->json($record);
    }

    /**
     * Get average test time of the last 24 hours
     *
     * TODO cache. memcached or redis if they are available on server

     * curl -X GET \
     * http://localhost:8080/app/test/stats/average/NetlabTestType:NetlabCropType:Kiwifruit:Fresh%20Weight
     */
    public function getTestAverage($test_id){
        $result = DB::table('test_statistics')
                ->select( DB::raw('AVG(avg_seconds) as avg_seconds'))
                ->join('test_types', 'test_types.id', '=', 'test_statistics.test_id')
                ->groupBy('test_id')
                ->where('test_types.name', '=', $test_id)
                ->whereRaw('test_statistics.end_time >= DATE_SUB(NOW(), INTERVAL 24 HOUR)')
                ->first();

        return response()->json($result);        
    }

}
