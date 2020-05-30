<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Base\ConfigurationController;
use App\Http\Controllers\Base\SagController;

class DevicesController extends Controller
{
    private $couch;

    public function __construct(){        
        $this->couch = SagController::getSagObj();
    }

    public function getAllDevices()
    {
        $res = $this->couch->get("_design/NetlabDevice/_view/id?include_docs=true");
        $jsonRes = json_encode($res);
        return $jsonRes;
    }
}
?>