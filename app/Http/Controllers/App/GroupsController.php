<?php

namespace App\Http\Controllers\App;

use App\Group;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class GroupsController extends Controller
{
    public function __construct()
    {
        // $this->middleware('jwt.auth');
    }

    public function create(Request $request){
        try{
            $input = $request->all();
            $groupCheck = Group::where('group',$input['group'])->get()->toArray();
            if ($groupCheck) {
                return response()->json(array('result' => false,'error' => "Group Already Exists"), 500);
            }
            $group = new Group();
            foreach($input as $key=>$value){
                $group->$key = $value;
            }
            $group->save();
            return response()->json(array('result' => true), 200);
        } catch (\Exception $e) {
            return response()->json(array('result' => false,'error' => $e->getMessage()), 500);
        }
    }

    public function get(Request $request){
        $input = $request->all();
        if (array_key_exists('id',$input)){
            return Group::where('id',$input['id'])->first();
        }
        return Group::all();
    }

    public function update(Request $request){
        $input = $request->all();
        try{
            $group = new Group();
            foreach($input as $key=>$value){
                $group->$key = $value;
            }
            $group->save();
            return response()->json(array('result' => true), 200);
        } catch (\Exception $e) {
        return response()->json(array('result' => false,'error' => $e->getMessage()), 500);
        }
    }

    public function delete(Request $request){
        $input = $request->all();

    }

    public function getTestAccessLevels (){
        try{
           $testGroups = Group::where("group_type","Access")->get();
           return response()->json(array('result' => true, 'data' => $testGroups), 200);
        }catch(\Exception $e){
            return response()->json(array('result' => false,'error' => $e->getMessage()), 500);
        }
    }

}