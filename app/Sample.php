<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Sample extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'samples';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     *
     */
    protected $fillable = [
        'sample_number',
        'status',
        'variety',
        'croptype',
        'itemcount',
        'samplesource',
        'area'
    ];
}
