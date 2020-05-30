<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class QCBreach extends Model
{
    protected $casts = [
         'id' => 'int',
         'event' => 'array'
    ];

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'qc_breach';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     *
     */
    protected $fillable = ['sample_number', 'event'];

    public static function bySample($sampleNumber)
    {
        $matchArray = ['sample_number' => $sampleNumber];
        $events = QCBreach::where($matchArray)->get();
        return $events;
    }

    public static function last2days()
    {
        $events = QCBreach::whereDate('created_at','>=', Carbon::now()->subDays(2)->toDateTimeString())->get();
        return $events;
    }

}
