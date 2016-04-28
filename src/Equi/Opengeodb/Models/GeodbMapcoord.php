<?php

namespace Equi\Opengeodb\Models;

use Illuminate\Database\Eloquent\Model;

class GeodbMapcoord extends Model
{
    
    public function GeodbTextdata(){
        return $this->hasMany('Equi\Opengeodb\Models\GeodbTextdata', "loc_id", "loc_id");
    }
    
    public function name(){
        return $this->GeodbTextdata->where("text_type", "500100000")->first();
    }
    
    public function kurz(){
        return $this->GeodbTextdata->where("text_type", "500500000")->first()->text_val;
    }
    
    public function level(){
        return $this->GeodbTextdata->where("text_type", "400200000")->first()->text_val;
    }
    
    public function parentloc_id(){
        return $this->GeodbTextdata->where("text_type", "400100000")->first()->text_val;
    }
}
