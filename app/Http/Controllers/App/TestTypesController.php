<?php

namespace App\Http\Controllers\App;

use App\TestType;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class TestTypesController extends Controller
{
    public function __construct(){
        // $this->middleware('jwt.auth');
    }

    public function index(){

    }

    public function get(Request $request){
        $input = $request->all();
        if (array_key_exists('id',$input)){
            return TestType::where('id',$input['id'])->first();
        }
        return TestType::all();
    }

}
