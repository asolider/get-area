<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class City extends Model
{
    protected $table = 'city';
    protected $guarded = [];

    public static function getChildrenCount($pid)
    {
        return static::from('area')->where('pid', $pid)->count();
    }

    public function province()
    {
        return $this->belongsTo('App\Model\Province', 'pid', 'id');
    }
}
