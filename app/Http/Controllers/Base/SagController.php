<?php

namespace App\Http\Controllers\Base;

use App\Http\Controllers;

class SagController extends Controllers\Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return MIXED
     */

    public static function getSagObj()
    {
        $settings = ConfigurationController::getCouchSettings();
        $loc = new \Sag($settings['address'], $settings['port']);
        if($settings['database'] != null ){
            return $dbo = $loc->setDatabase($settings['database']);
        }else{
           return "Please check your configuration file and make sure all properties are correct.(CNF->DB)";
        }
    }

}
