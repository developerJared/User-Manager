<?php

namespace App\Http\Controllers\App;

use App\Role;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class RolesController extends Controller
{
    public function __construct()
    {
         //$this->middleware('jwt.auth');
    }

    public function get(Request $request){
        $input = $request->all();
        if (array_key_exists('id',$input)){
            return Role::where('id',$input['id'])->first();
        }
        return Role::all();
    }

    public function create(Request $request){
        $input = $request->all();
        $role = New Role();
        $role->name = $input['role'];

    }
    public function update(Request $request){
        $input = $request->all();

    }
    public function delete(Request $request){
        $input = $request->all();

    }

}