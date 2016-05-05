<?php

namespace Equi\Opengeodb\Models;

use Illuminate\Database\Eloquent\Model;

class GeodbCoordinate extends Model
{
    public function calculatedistance($lon, $lat){
        return round(ACOS((SIN(deg2rad($lat))*SIN(deg2rad($this->lat))) + (COS(deg2rad($lat))*COS(deg2rad($this->lat))*COS(deg2rad($this->lon)-deg2rad($lon)))) * 6371.110 ,2);
    }
}
