<?php

namespace Equi\Opengeodb\Models; 

class GeodbMaster {    
    public $loc_id;
    private $textdata;
    private $_GeodbTextdata; 
    private $_GeodbMapcoord; 
    private $_GeodbCoordinate; 
    
    public function __construct($loc_id = null){
        if (!empty($loc_id))
            $this->loc_id = $loc_id;
        $this->textdata = GeodbTextdata::select("loc_id");
    }
    
    public function searchByPLZ($plz){
        $this->textdata->orWhere(function ($query) use($plz){
            $query->where("text_type", "500300000")->where("text_val", $plz);
        });
        return $this;
    }
    
    public function searchByName($name){
        $this->textdata->orWhere(function ($query) use($name){
            $query->where("text_type", "500100000")->where("text_val", $name);
        });
        return $this;
    }
    
    public function searchByKurz($kurz){
        $this->textdata->orWhere(function ($query) use($kurz){
            $query->where("text_type", "500500000")->where("text_val", $kurz);
        });
        return $this;
    }
    
    public function searchByLoc_id($loc_id){
        $this->textdata->orWhere(function ($query) use($loc_id){
            $query->where("loc_id", $loc_id);
        });
        return $this;
    }
    
    public function first(){
        $this->textdata = $this->textdata->first();
        if (empty($this->textdata))
            return null;
        return new GeodbMaster($this->textdata->loc_id);
    }

    public function get(){
        $this->textdata->get();
        $Geomasters = [];
        foreach($this->textdata->unique('loc_id') as $locs){
            $Geomasters[] = new GeodbMaster($locs->loc_id);
        }
        return collect($Geomasters);
    }
    
    public function GeodbMapcoord(){ 
        if (empty($this->_GeodbMapcoord))
            $this->_GeodbMapcoord = GeodbMapcoord::where("loc_id", $this->loc_id)->first();
        return  $this->_GeodbMapcoord;
    }
    
    public function GeodbCoordinate(){
        if (empty($this->_GeodbCoordinate))
            $this->_GeodbCoordinate = GeodbCoordinate::where("loc_id", $this->loc_id)->first();
        return  $this->_GeodbCoordinate;
    }
    
    public function GeodbTextdata(){
        if (empty($this->_GeodbTextdata))
            $this->_GeodbTextdata = GeodbTextdata::where("loc_id", $this->loc_id)->get();
        return  $this->_GeodbTextdata;
    }
    
    public function name(){
        if (!isset($this->GeodbTextdata()->where("text_type", "500100000")->first()->text_val))
            return "";
        return $this->GeodbTextdata()->where("text_type", "500100000")->first()->text_val;
    }
    
    public function kurz(){  
        return $this->GeodbTextdata()->where("text_type", "500500000")->first()->text_val;
    }
    
    public function level(){  
        if (!isset($this->GeodbTextdata()->where("text_type", "400200000")->first()->text_val))
            return "";
        return $this->GeodbTextdata()->where("text_type", "400200000")->first()->text_val;
    }
    
    public function parentloc_id(){  
        return $this->GeodbTextdata()->where("text_type", "400100000")->first()->text_val;
    }
}
