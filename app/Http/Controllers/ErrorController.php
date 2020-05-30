<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;

class ErrorController extends Controller
{
    /**
     * Endpoint for Logging Crashes in NL2 App
     *
     * Creates File in FS
     */
    public function logCrash($args)
    {
        $myfile = fopen("CrashLog.txt", "w") or die("Unable to open file!");
        $txt = "$args\n";
        fwrite($myfile, $txt);
        fclose($myfile);
    }


}
