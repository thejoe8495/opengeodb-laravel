<?php

namespace Equi\Opengeodb\Models;

use Illuminate\Database\Eloquent\Model;

class GeodbTextdata extends Model
{
    protected $table = 'geodb_textdata';
    
    public function GeodbMapcoord(){
        return $this->belongsTo('Equi\Opengeodb\Models\GeodbMapcoord',"loc_id", "loc_id");
    }
    
    public function GeodbMapcoords(){
        return GeodbMapcoord::join("geodb_textdata","geodb_textdata.loc_id", "=", "geodb_mapcoords.loc_id")->orWhere(
            function($query){
                $query->where("geodb_textdata.loc_id", $this->loc_id)->where("geodb_textdata.text_type", "400100000");
            })->orWhere(
            function($query){
                $query->where("geodb_textdata.text_val", $this->loc_id)->where("geodb_textdata.text_type", "400100000");
            })->get();
    }
    
    public function GeodbCoordinate(){
        return $this->belongsTo('Equi\Opengeodb\Models\GeodbCoordinate',"loc_id", "loc_id");
    }
    
    public function GeodbTextdata(){
        return $this->hasMany('Equi\Opengeodb\Models\GeodbTextdata', "loc_id", "loc_id");
    }
    
    public function name(){
        return $this->GeodbTextdata->where("text_type", "500100000")->first()->text_val;
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
