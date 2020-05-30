<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TestStatistics extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'test_statistics';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     *
     */
    protected $fillable = [
        'sample_number',
        'legacy_staff_type_id',
        'test_id',
        'tray',
        'tray_size',
        'scan_time',
        'start_time',
        'end_time',
        'avg_seconds',
        'test_down_time'
    ];

    public static function updateAverage($obj)
    {
        if($obj->scan_time && $obj->end_time && $obj->start_time){
            
            if($obj->tray_size === 30){
                $obj->avg_seconds = (strtotime($obj->end_time) - strtotime($obj->scan_time)) - $obj->test_down_time;
            }else{
                // normalize to 30 fruits
                $oneFruit = ((strtotime($obj->end_time) - strtotime($obj->start_time)) - $obj->test_down_time) / $obj->tray_size;
                $toStart = strtotime($obj->start_time) - strtotime($obj->scan_time);
                $obj->avg_seconds = $toStart + $oneFruit * 30;
            }
        }
    }


}
