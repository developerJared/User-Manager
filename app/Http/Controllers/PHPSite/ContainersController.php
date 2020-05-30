<?php

namespace App\Http\Controllers\PHPSite;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Container;

class ContainersController extends Controller
{
    public function __construct(){

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        /*validatations make:request this could be
        * Quite useful even for api validations
        */
        $this->validate($request,['barcode'=>'required', 'weight'=>'required', 'containerType'=>'required']);
        Container::create($request->all());
    }

    /**
     * Display the specified resource.
     *
     * @param $request
     * @return \Illuminate\Http\Response
     */
    public function getById(Request $request)
    {
        $containerBarcode = $request->containerBarcode;
        try{
            return Container::where('barcode',$containerBarcode)->get()->toArray();
        }catch(\Exception $e){
            return "Error: $e";
        }
    }

    /**
     * Display the specified resource.
     *
     * @param $request
     * @return \Illuminate\Http\Response
     */
    public function getByType(Request $request)
    {
        $containerType = $request->containerType;

        try {
            return Container::where('containerType', $containerType)->get()->toArray();
        }catch(\Exception $e){
            return "Error:  $e";
        }
    }

    /**
     * Display the specified resource.
     * Shows All Containers
     *
     * if null returns all. Container type follows NL2 convention
     * @return \Illuminate\Http\Response
     */
    public function getAll()
    {
        Container::all()->toArray();
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
