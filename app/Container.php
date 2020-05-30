<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Container extends Model
{
   // public $timestamps = false;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'containers';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     *
     */
      protected $fillable = ['barcode','weight','nl_id','containerType'];


    public static function byTypeBarcode($type, $barcode)
    {
        $matchArray = ['containerType' => $type, 'barcode' => $barcode];
        $container = Container::where($matchArray)->first();
        return $container;
    }

    public static function multipleByTypeBarcode($type, $barcodes){

        $barcodes = explode(",",$barcodes);
        $containers = Container::where('containerType',$type)->whereIn('barcode',$barcodes)->get()->toArray();
        return $containers;
    }

    public static function get($params)
    {
        if ($params != null) {
            return Container::where($params)->first();
        }
    }

    public static function preload($type,$start,$end){

        if($start === $end){
            $container = new Container;
            $container->barcode = $start;
            $container->weight = 1;
            $container->containerType = "NetlabContainerType:".$type;
            $container->save();
            return true;
        }
        while ($start <= $end) {
            $container = new Container;
            $container->barcode = $start;
            $container->weight = 1;
            $container->containerType = "NetlabContainerType:".$type;
            $container->save();
            $start++;
        }
            return true;
    }
}

