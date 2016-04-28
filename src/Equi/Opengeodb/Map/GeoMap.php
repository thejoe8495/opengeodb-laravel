<?php
namespace Equi\Opengeodb\Map;
use Equi\Opengeodb\Models\GeodbMaster;

class GeoMap extends map {

    private $loc_id;
    private $colors = [];

    private $latitudeMin;
    private $latitudeMax;
    private $longitudeMin;
    private $longitudeMax;
    private $objects = array();
    private $imageMap = array();
    private $radius = 4;

    /**
    * constructor
    *
    * @param   mixed  $x  image-width (int) or path to image (string)
    * @param   int    $y  image-height
    * @return  void
    */
    public function __construct($loc_id, $breite=810, $loc_idadm1 = null) {
        $this->loc_id = $loc_id;
        $geo = (new GeodbMaster())->searchByLoc_id($loc_id)->first();
        if ($geo->level() == 3 && empty($loc_idadm1)){
            $loc_id = $geo->parentloc_id();
            $loc_idadm1 = $geo->loc_id;
        }
        
        $faktor = $breite / ($geo->GeodbMapcoord()->tolat - $geo->GeodbMapcoord()->fromlat)/ 0.75;
        $laenge = ($geo->GeodbMapcoord()->tolon - $geo->GeodbMapcoord()->fromlon) * $faktor;
        
        parent::__construct($breite, $laenge);
        $mapcolors = \Config::get('opengeodb.mapcolor');
        foreach($mapcolors as $key => $color){
            $this->colors[$key] = $this->color($color[0], $color[1], $color[2]);

        } 
        $this->setRange($geo->GeodbMapcoord()->fromlat, $geo->GeodbMapcoord()->tolat, $geo->GeodbMapcoord()->fromlon, $geo->GeodbMapcoord()->tolon);
        $this->addDataFile("/" .(!empty($loc_idadm1)?$loc_id."-".$loc_idadm1:"105-"."kreise") .".e00", "kreis");
        $this->addDataFile("/$loc_id-bund.e00", "bund");
        $this->addDataFile("/$loc_id.e00", "land");
    }

    /**
    * Sets the range of the map from overgiven degree-values
    *
    * container for API compatibility with PEAR::Image_GIS
    *
    * @access  public
    * @param   float   $x1
    * @param   float   $x2
    * @param   float   $y1
    * @param   float   $y2
    * @return  void
    */
    public function setRange($x1,$x2,$y1,$y2) {
        $this->set_range($x1, $x2, $y1, $y2);
    }

    /**
    * Sets the range of the map from overgiven degree-values-array
    *
    * @access  public
    * @param   array   $rangeArray
    * @return  void
    */
    public function setRangeByArray($rangeArray) {
        $this->set_range($rangeArray[0], $rangeArray[1], $rangeArray[2], $rangeArray[3]);
    }

    /**
    * Calculates distances between the corners and returns an ratio or values
    *
    * @access  public
    * @param   array   $rangeArray
    * @param   int     $width      preseted width, basis for height
    * @param   int     $height     vice versa
    * @return  array   width and height
    */
    public function getSizeByRange($rangeArray, $width = 0, $height = 0) {
        $eol = new Geo_Object("eol", $rangeArray[3], $rangeArray[0]);
        $eor = new Geo_Object("eor", $rangeArray[3], $rangeArray[1]);
        $eul = new Geo_Object("eul", $rangeArray[2], $rangeArray[0]);
        $eur = new Geo_Object("eur", $rangeArray[2], $rangeArray[1]);
        $ns1 = abs($eol->getDistance($eul));
        $ns2 = abs($eor->getDistance($eur));
        $we1 = abs($eol->getDistance($eor));
        $we2 = abs($eul->getDistance($eur));
        $ns = ($ns1 + $ns2) / 2;
        $we = ($we1 + $we2) / 2;
        $ratio = $we / $ns;
        if (($width == 0) && ($height == 0)) return array($ratio, 1);
        if (($width != 0) && ($height == 0)) return array($width, round($width/$ratio));
        if (($width == 0) && ($height != 0)) return array(round($height * $ratio), $height);
        $calcHeight = round($width/$ratio);
        $calcWidth = round($height * $ratio);
        if ($calcHeight <= $height) return array($width, $calcHeight);
        return array($calcWidth, $height);
    }

    /**
    * Sets the range of the map from overgiven GeoObjects
    *
    * @access  public
    * @param   array   &$geoObjects  Array of GeoObjects
    * @param   float   $border       degrees
    * @return  void
    * @see     setRange(),setRangeByGeoObject()
    */
    public function setRangeByGeoObjects($geoObjects,$border=0.1) {
        foreach($geoObjects AS $geoObject) {
            $this->_setRangeByGeoObject($geoObject);
        }

        $this->setRange(
            $this->longitudeMin - $border,
            $this->longitudeMax + $border,
            $this->latitudeMin - $border,
            $this->latitudeMax + $border
        );
    }

    /**
    * Sets the range of the map from an overgiven GeoObject
    *
    * @access  public
    * @param   array   &$geoObject  GeoObject
    * @param   float   $border      degrees
    * @return  void
    * @see     setRange(),setRangeByGeoObjects()
    */
    public function setRangeByGeoObject($geoObject,$border=0.1) {
            $this->_setRangeByGeoObject($geoObject);

        $this->setRange(
            $this->longitudeMin - $border,
            $this->longitudeMax + $border,
            $this->latitudeMin - $border,
            $this->latitudeMax + $border
        );
    }
    
    private function _setRangeByGeoObject($geoObject,$border=0.1) {
        if (!$this->longitudeMin || ($geoObject->lon < $this->longitudeMin)) 
            $this->longitudeMin = $geoObject->lon;
        if (!$this->longitudeMax || ($geoObject->lon > $this->longitudeMax))
            $this->longitudeMax = $geoObject->lon;
        if (!$this->latitudeMin || ($geoObject->lat < $this->latitudeMin)) 
            $this->latitudeMin = $geoObject->lat;
        if (!$this->latitudeMax || ($geoObject->lat > $this->latitudeMax)) 
            $this->latitudeMax = $geoObject->lat;  
    }

    /**
    * Adds a GeoObject to the map
    *
    * @access  public
    * @param   array   &$geoObject  GeoObject
    * @param   string  $color
    * @param   int     $radius
    * @return  void
    * @see     addGeoObjects()
    */
    function addGeoObject($geoObject, $zwei, $art, $color='black', $radius=0) {
        $x = round($this->scale($geoObject->laenge, 'x'));
        $y = round($this->scale($geoObject->breite, 'y'));
        if (($x > $this->size_x) || ($y > $this->size_y)) return false;
        $hasDrawn = false;
        if (function_exists("imagefilledellipse")) {
            $hasDrawn = imagefilledellipse($this->img, $x, $y, ($radius*2), ($radius*2), $this->colors[$color]);
        }
        if (!$hasDrawn) {
            for($i=1;$i<=$radius;$i++) {
                ImageArc($this->img, $x, $y, $i, $i, 0, 360, $this->colors[$color]);
            }
        }
        $this->imageMap[] = array(
            "name"  => ($zwei->stadt?$zwei->stadt . "/":"").$zwei->name,
            "x"     => $x,
            "y"     => $y,
            "r"     => $radius?$radius:$this->radius,
            "o"     => $geoObject,
            "count" =>  1,
            "color" => $color,
            "art"   => $art,
            "id"    => $zwei->id
        );
    }

    /**
    * Adds a GeoObject to the map, respects already added objects and increases     * drawn circles, tolerance is the last radius
    *
    * @access  public
    * @param   array   &$geoObject  GeoObject
    * @param   string  $color
    * @param   array   $radii different sizes for different count of GeoObjects at one spot
    * @return  void
    */
    public function addGeoObjectIncrease($geoObject, $zwei ,$art, $color='black', $radii=array(1=>1, 2=>3, 3=>5, 4=>6, 1000=>4)) {
        $x = round($this->scale($geoObject->laenge, 'x'));
        $y = round($this->scale($geoObject->breite, 'y'));
        $radii=array(1=>2, 2=>3, 3=>4, 4=>5, 5=>6, 6=>4);
        $tolerance = end($radii);
        $wasFound = false;
        for ($imc = 0; $imc<count($this->imageMap); $imc++) {
            if (($this->imageMap[$imc]['x'] <= ($x + $tolerance))&& ($this->imageMap[$imc]['x'] >= ($x - $tolerance)) && ($this->imageMap[$imc]['y'] <= ($y + $tolerance)) && ($this->imageMap[$imc]['y'] >= ($y - $tolerance))) {
                if (strpos($this->imageMap[$imc]['name'], $zwei->name) === false) {
                    $this->imageMap[$imc]['name'] .= ",$zwei[stadt]/$zwei[name]";
                    $this->imageMap[$imc]['id'] .= ".$zwei[id]";
                    $this->imageMap[$imc]['art'] .= ".$art";
                }
                $this->imageMap[$imc]['count']++;
                if (isset($radii[$this->imageMap[$imc]['count']])) {
                    $hasDrawn = false;
                    if (function_exists("imagefilledellipse")) {
                        $hasDrawn = imagefilledellipse($this->img, $this->imageMap[$imc]['x'], $this->imageMap[$imc]['y'], ($radii[$this->imageMap[$imc]['count']]*2), ($radii[$this->imageMap[$imc]['count']]*2),$this->colors[$this->imageMap[$imc]['color']]);
                    }
                    if (!$hasDrawn) {
                        for($i=$this->imageMap[$imc]['r'];$i<=$radii[$this->imageMap[$imc]['count']];$i++) {
                            imagearc($this->img, $this->imageMap[$imc]['x'], $this->imageMap[$imc]['y'], $i, $i, 0, 360, $this->color[$this->imageMap[$imc]['color']]);
                        }
                    }
                    $this->imageMap[$imc]['r'] = $radii[$this->imageMap[$imc]['count']];
                }
                $wasFound = true;
                break;
            }
        }
        if (!$wasFound) $this->addGeoObject($geoObject, $zwei , $color, $radii[1]);
    }

    /**
    * Adds GeoObjects to the map
    *
    * @access  public
    * @param   array   &$geoObjects  Array of GeoObjects
    * @param   string  $color
    * @return  void
    * @see     addGeoObject()
    */
    public function addGeoObjects(&$geoObjects,$color='black') {
        foreach($geoObjects AS $geoObject) {
            $this->addGeoObject($geoObject,$color);
        }
    }

    /**
    * Saves the image
    *
    * container for API compatibility with PEAR::Image_GIS
    *
    * @access  public
    * @param   string  $file
    * @return  void
    * @see     map::dump()
    */
    function saveImage($file = null) {
        if (empty($file))
            $this->dump(\Config::get('opengeodb.storagemap') . "/" . $this->loc_id . ".png");
        else
            $this->dump($file);
    }
    
    function getImagePath() {
        return \Config::get('opengeodb.storagemap') . "/" . $this->loc_id . ".png";
    }
    
    /**
    * Saves the image
    *
    * container for API compatibility with PEAR::Image_GIS
    *
    * @access  public
    * @param   string  $file
    * @return  void
    * @see     map::dump()
    */
    function saveMapJson($file = null) {
        if (empty($file))
            \Storage::put(\Config::get('opengeodb.storagemap') . "/" . $this->loc_id . ".json", json_encode($this->imageMap));
        else
            \Storage::put($file, json_encode($this->imageMap));
    }
        
    /**
    * Creates an image map (html)
    *
    * @access  public
    * @param   string  $name  name of the ImageMap
    * @return  string  html
    */
    function getArrayMapdata() {
        return $this->imageMap;
    }

    /**
    * Creates an image map (html)
    *
    * @access  public
    * @param   string  $name  name of the ImageMap
    * @return  string  html
    */
    function getImageMap($name="map") {
        $html = '<map name="'.$name.'">';
        foreach($this->imageMap as $koord) {
            $nasi = explode(".",$koord[art]); 
            $goreng = explode(".",$koord[id]); 
            $hopsl = explode(",",$koord[name]);
            //$hopsl = str_replace('"','\"',$hopsl)
            if (count($nasi)>1){
                $html .= "<area shape='circle' coords='".round($koord['x']).','.round($koord['y']).','.$koord[r] .'\' href=\'#\' onmouseover="return overlib(\'<Table>';
                for ($x =0; $x < count($nasi);$x++){$html .= "<TR><TD class=kartenbox><a href=/$nasi[$x]-$goreng[$x].html><nobr>$hopsl[$x]</nobr></a></TD></TR>";}
                $html .= '</table>\',STICKY, MOUSEOFF);" '.$hopsl[$x].'onmouseout="return nd();">'."\n";
            }else{
                $html .= "<area shape='circle' coords='".round($koord['x']).','.round($koord['y']).','.$koord[r] .'\' href=\'#\' onmouseover="return overlib(\'<Table><TR><TD class=kartenbox><a href=/'.$koord[art].'-'.$koord[id].'.html><nobr>'.$koord[name].'</nobr></a></TD></TR></table>\',STICKY, MOUSEOFF);" onmouseout="return nd();">'."\n";
            }
        }
        $html.='</map>';
        return $html;
    }

    /**
    * Creates an image map (html)
    *
    * Attributes is an associate array, where the key is the attribute.
    * array("alt"=>"http://example.com/show.php?id=[id]") where id is a dbValue     *
    * @access  public
    * @param   string  $name           name of the ImageMap
    * @param   array   $attributes     attributes for the area
    * @return  string  html
    */
    function getImageMapExtended($name="map", $attributes=array(), $areas="") {
        $defaultAttributes = array("href"=>"#", "alt"=>"");
        $attributes = array_merge($defaultAttributes, $attributes);
        $html = "<map name=\"".$name."\">\n";
        foreach($this->imageMap as $koord) {
            $theObject = $koord['o'];
            $im_array = array(
                "imagemap_name"     => $koord['name'],
                "imagemap_x"        => $koord['x'],
                "imagemap_y"        => $koord['y'],
                "imagemap_r"        => $koord['r'],
                "imagemap_count"    => $koord['count'],
                "imagemap_color"    => $koord['color']
            );
            $theObject->dbValues = array_merge($theObject->dbValues, $im_array);            $attributeList = array();
            foreach($attributes as $attKey=>$attVal) {
                if ($attKey == "href") {
                    $attributeList[] = $attKey."=\"".
                    preg_replace("|(\[)([^\]]*)(\])|ie", '(isset($theObject->dbValues[\2])?urlencode($theObject->dbValues[\2]):"")', $attVal).
                    "\"";
                } else {
                    $attributeList[] = $attKey."=\"".
                    preg_replace("|(\[)([^\]]*)(\])|ie", '(isset($theObject->dbValues[\2])?$theObject->dbValues[\2]:"")', $attVal).
                    "\"";
                }
            }
            $html .= "<area shape=\"circle\" coords=\"".round($koord['x']).",".round($koord['y']).",".$koord['r']."\" ".implode(" ", $attributeList).">\n";
        }
        $html.=$areas;
        $html.='</map>';
        return $html;
    }

    /**
    * Adds an e00-file to the image
    *
    * container for API compatibility with PEAR::Image_GIS
    *
    * @access  public
    * @param   string  $data  path to e00-file
    * @return  boolean
    * @see     map::draw()
    */
    public function addDataFile($data, $color='black') {
        if (strtolower(substr($data, -4)) == ".ovl") {
            return $this->addOvlFile($data, $color);
        }
        if (\Storage::exists(\Config::get('opengeodb.storagee00') . $data)) {
            $this->adde00File($data, $this->colors[$color]);
            return true;
        } else {
            return false;
        }
    }

    /**
    * Adds an ovl-file to the image
    *
    * @access  public
    * @param   string  $data  path to ovl-file
    * @return  boolean
    * @see     map::draw()
    */
    private function addOvlFile($data, $color='black') {
        if (\Storage::get(\Config::get('opengeodb.storagee00') . $data)) {
            $ovlRows = file($data);
            $importantRows = array();
            foreach ($ovlRows as $aRow) {
                if (strpos($aRow, "Koord") == 1) {
                    $importantRows[] = trim($aRow);
                }
            }
            $pointArray = array();
            $lastIndex = 0;
            $lastX = 0;
            $lastY = 0;
            for ($i = 0; $i < count($importantRows); $i += 2) {
                list($cruft, $data) = explode("Koord", $importantRows[$i]);
                list($idA, $XA) = explode("=", $data);
                list($cruft, $data) = explode("Koord", $importantRows[$i + 1]);
                list($idB, $YB) = explode("=", $data);
                $x = $this->scale($XA, "x");
                $y = $this->scale($YB, "y");
                if ($idA > $lastIndex) {
                    imageline($this->img, $lastX, $lastY, $x, $y, $this->color[$color]);
                }
                $lastIndex = $idA;
                $lastX = $x;
                $lastY = $y;
            }
            return true;
        } else {
            return false;
        }
    }

    function adde00File($data, $col) {
        $num_records = 0;
        $ln = 0;
        $filedata = explode("\n", \Storage::get(\Config::get('opengeodb.storagee00') . $data));
        foreach($filedata as $line){ 
            $ln ++;   
            # a node definition
            if ($num_records == 0 && preg_match("#^\s+([0-9]+)\s+([-0-9]+)\s+([0-9]+)\s+([0-9]+)\s+([0-9]+)\s+([0-9]+)\s+([0-9]+)#", $line, $a)) {
                $num_records = $a[7];

                $pl['x'] = -1; $pl['y'] = -1;

            # 2 coordinates
            } else if ($num_records && preg_match("#^ *([-+]?[0-9]\.[0-9]{7}E[-+][0-9]{2}) *([-+]?[0-9]\.[0-9]{7}E[-+][0-9]{2}) *([-+]?[0-9]\.[0-9]{7}E[-+][0-9]{2}) *([-+]?[0-9]\.[0-9]{7}E[-+][0-9]{2})#", $line, $a)) {

                if ($pl['x'] != -1 && $pl['y'] != -1) {
                    $this->draw_clipped($pl['x'], $pl['y'], $a[1], $a[2], $col);
                }

                $num_records--;
                $this->draw_clipped($a[1], $a[2], $a[3], $a[4], $col);
                $pl["x"] = $a[3]; $pl["y"] = $a[4];
                $num_records--;

            # 1 coordinate
            } else if ($num_records && preg_match("#^ *([-+]?[0-9]\.[0-9]{7}E[-+][0-9]{2}) *([-+]?[0-9]\.[0-9]{7}E[-+][0-9]{2})#", $line, $a)) {

                if ($pl['x'] != -1 && $pl['y'] != -1) {
                    $this->draw_clipped($pl['x'], $pl['y'], $a[1], $a[2], $col);
                    $pl["x"] = $a[1]; $pl["y"] = $a[2];
                }

                $num_records--;
            # done
            } else if ($ln > 2) {
                break;
            }
        }
    }
}
?>
