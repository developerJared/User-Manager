<?php

namespace App\Http\Controllers\App;

use App\Container;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ContainersController extends Controller
{
    public function __construct(){
        // $this->middleware('jwt.auth');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $input = $request->all();
        $container = [];
        $container['barcode'] = $input['barcode'];
        $container['weight'] = $input['weight'];
        $container['containerType'] = $input['containerType'];
        if($container['barcode'] != null && $container['weight'] != null && $container['containerType'] != null){
            $res = Container::create($container);
            return response()->json(["result"=>$res], 200) ;
        }else{
            return response()->json("Invalid Fields", 406) ;
        }
    }

    /**
     * Check Container does Not already exist.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function checkContainer($type, $barcode)
    {
        $container = Container::byTypeBarcode($type,$barcode);
        if($container != null){
            return response()->json(["result"=>false], 200) ;
        }else{
            return response()->json(["result"=>true], 200) ;
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  string type
     * @param string barcode
     * @return JSON container Object
     */
    public function byTypeBarcode($type,$barcode)
    {
        $container = Container::byTypeBarcode($type,$barcode);
        if($container != null){
            if(isset($container->weight)){
                $container->weight = (float)$container->weight;
            }
            return $container;
        }else{
            return response()->json("Container Not Found", 404) ;
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  string type
     * @param string barcode
     * @return JSON container Object
     */
    public function getContainers($type,$barcodes = ""){
        return Container::multipleByTypeBarcode($type,$barcodes);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  Request  $request
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $input = $request->all();
        $container = [];
        $container['barcode'] = $input['barcode'];
        $container['weight'] = $input['weight'];
        $container['containerType'] = $input['containerType'];
        if($container['barcode'] != null && $container['weight'] != null && $container['containerType'] != null){
            $res = Container::where('barcode',$container['barcode'])
                ->where('containerType',$container['containerType'])
                ->update(['weight'=>$container['weight']]);
            return response()->json(["result"=>$res], 200) ;
        }else{
            return response()->json("Invalid Fields", 406) ;
        }
    }

}
