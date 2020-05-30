<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Base\ConfigurationController as Config;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class CropsController extends Controller
{
    public function __construct(){
        // $this->middleware('jwt.auth');
    }

    public function index(){

    }

    public function get(){
       $config = Config::getCurrentCropSeason();
       return $config;
    }

}