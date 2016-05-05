<?php

namespace Equi\Opengeodb\Models;

use Illuminate\Database\Eloquent\Model;

class GeodbMapcoord extends Model
{
    private $_geodbmaster;
    public function GeodbTextdata(){
        return $this->hasMany('Equi\Opengeodb\Models\GeodbTextdata', "loc_id", "loc_id");
    }
    
    public function GeodbMaster(){
        if (empty($this->_geodbmaster))
            $this->_geodbmaster = new GeodbMaster($this->loc_id);
        return $this->_geodbmaster;
    }
}
