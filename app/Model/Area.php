<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Area extends Model
{
    protected $table = 'area';
    protected $guarded = [];

    public static function getChildrenCount($pid)
    {
        return static::from('town')->where('pid', $pid)->count();
    }



    public function city()
    {
        return $this->belongsTo('App\Model\City', 'pid', 'id');
    }
}
