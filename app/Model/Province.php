<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Province extends Model
{
    protected $table = 'province';

    protected $guarded = [];

    public function citys()
    {
        return $this->hasMany('App\Model\City', 'pid', 'id');
    }

    public static function getChildrenCount($pid)
    {
        return static::from('city')->where('pid', $pid)->count();
    }
}
