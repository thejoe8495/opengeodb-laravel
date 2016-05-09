# Opengeodb Laravel

<a rel="license" target="_blank" href="http://creativecommons.org/licenses/by-nd/3.0/de/"><img alt="Creative Commons Lizenzvertrag" style="border-width:0" src="https://i.creativecommons.org/l/by-nd/3.0/de/88x31.png" /></a><br />Dieses Werk ist lizenziert unter einer <a rel="license" href="http://creativecommons.org/licenses/by-nd/3.0/de/" target="_blank">Creative Commons Namensnennung-Keine Bearbeitung 3.0 Deutschland Lizenz</a>.

## Über


## Installieren
### Composer /Artisan
```
composer require equi/opengeodb-laravel:
```

oder in die composer.json die Zeile "equi/opengeodb-laravel": "~6.0", hinzufügen  
```
...
"require": {
        "php": ">=5.5.9",
        "laravel/framework": "5.2.*",
        ....
        "equi/opengeodb-laravel": "~6.0",
        ...
    },
    ...
```
```
composer update
php artisan vendor:publish
php artisan optimize
```

### Fehlende Dateien suchen
[OpenGeoDB](http://opengeodb.giswiki.org/wiki/OpenGeoDB) -> [Downloads](http://www.fa-technik.adfc.de/code/opengeodb/)
Welche Dateien braucht ihr?  
Pflicht:
```
opengeodb-begin.sql
opengeodb-end.sql
opengeodb_hier.sql
changes.sql
```

Optional je Nachdem welches Land ihr benötigt paarweiße laden:  
```
AT.sql
AThier.sql
BE.sql
BEhier.sql
CH.sql
CHhier.sql
DE.sql
DEhier.sql
LI.sql
LIhier.sql
Extra.sql    // &Uuml;bergeordnete inhalte (Europa, Amerika, ...) und dazugeh&ouml;rige Sprachen (Deutschland = Germany ...)
```

Die Dateien scheinen veraltet diese werden aber in changes.sql aktualisiert  

### Konfiguration config/opengeodb.php

```
return [
    // Storage/app/.....
    'storagemap' => "/opengeodb/map",
    'storagee00' => "/opengeodb/e00",
    'storageopengodbsql' => "/opengeodb/sql",
    
    'mapcolor' => [
        'black'=>[0, 0, 0], 
        'white'=>[255, 255, 255], 
        'red'=>[255, 0, 0], 
        'green'=>[178, 237, 90], 
        'blue'=>[148, 208, 255], 
        'grey'=>[148, 208, 255], 
        'darkgrey'=>[148, 208, 255], 
        'yellow'=>[148, 208, 255], 
        'pink'=>[148, 208, 255],
         
        'land'=>[20, 20, 20], 
        'bund'=>[125, 125, 125], 
        'kreis'=>[200, 200, 200], 
    ],
    // Anzahl => radisgr&ouml;&szlig;e
    'radiusdata' => [
        1 => 7,
        2 => 10,
        5 => 14,
    ]
    
];
```

### Datenbanken erstellen und füllen
```
php artisan migrate --seed
```
Für manche PHP Konfiguration ist die de.sql zu groß hierfür könnt ihr folgendes versuchen:
```
php -d memory_limit=256M artisan migrate --seed
```

## Einbinden/Benutzen

### Karte zeichnen
Das Beispiel zeigt ein Controller der in der routes.php auf /Karte/ reagiert.  
```
namespace App\Http\Controllers;

use App\Http\Requests;
use Illuminate\Http\Request;
use App\Helpers\Controller;
use App\Models\Thermal;
use Equi\Opengeodb\Map\GeoMap;
use Equi\Opengeodb\Models\GeodbMapcoord; 
use Equi\Opengeodb\Models\GeodbTextdata;

class MapController extends Controller
{

    /**
    * Show the application dashboard.
    *
    * @return \Illuminate\Http\Response
    */
    public function getIndex() { 
        $this->_data["demaps"] = GeodbTextdata::where("loc_id", "105")->first()->GeodbMapcoords();
        $this->_data["atmaps"][] = GeodbMapcoord::where("loc_id", "106")->first();
        $this->_data["chmaps"][] = GeodbMapcoord::where("loc_id", "107")->first();
        $this->_data["title"] = "Liste aller verf&uuml;gbaren Karten";
        return view('maps/map', $this->_data);
    }

    public function getShow($loc_id){
        return $this->zeichnethumb($loc_id);
    }

    public function getShowbig($loc_id){
        $map = $this->zeichne($loc_id);
        return \Response::make(\Storage::get($map->getImagePath()))->header('Content-Type', 'image/png');
    }
    
    public function getJson($loc_id){
        $map = $this->zeichne($loc_id);
        $data = $map->getArrayMapdata();
        for($i=0;$i < count($data); $i++){
            $datalinks = "";
            foreach($data[$i]["objects"] as $object){
                $datalinks .= "<a href='" . \URL::asset("/thermen/show/" . $object->id) . "' >" . $object->name . " (" . $object->stadt . ")</a><br>";
            }
            $data[$i]["objects"] = $datalinks;
        }
        return \Response::json(["data" => $data, "id" => $loc_id])->header('Content-Type', 'json');
    }

    private function zeichne($loc_id){
        $map = new GeoMap($loc_id, 1090);
        if (!$map->mapalreadyexists()) {
            $thermen = Thermal::all();
            foreach($thermen as $therme){
                $map->addGeoObjectIncrease($therme->lon, $therme->lat, $therme->id, $therme, $therme->artbad); 
            }
            $map->saveMapJson();
            $map->saveImage();
        }
        return $map;
    }
    
    private function zeichnethumb($loc_id){
        $image = \Config::get('opengeodb.storagemap')."/thumb" . $loc_id . ".png";
        if (!\Storage::exists($image)) {
            $map = new GeoMap();
            $map->createMapAfterLoc_id($loc_id, 250);
            $map->saveImage(storage_path("app".\Config::get('opengeodb.storagemap')."/thumb" . $loc_id . ".png" ));
        }
        return \Response::make(\Storage::get($image))->header('Content-Type', 'image/png');
    }
    
}
```
Mein aktueller weg um die Ladezeiten gering zu halten. Dies ist alles in der map.blade.php:
1. Um nur die Karte von Deutschland anzuzeigen kann einfach /Karte/show/105 (250px) aufgerufen werden.
    - &lt;img src="{{URL::Asset('/karte/show/' . $demaps[$x]->loc_id)}}" alt="Karte {{$demaps[$x]->GeodbMaster()->name()}}">
    - Bitte in eine for-schleife setzen oder das $x mit einer zahl ersetzen.
2. In der Funktion /Karte/showbig/105 (1090px) werden noch die Punkte hinzugezeichnet.  
    - &lt;img src="{{URL::Asset('/karte/showbig/' . $demaps[$x]->loc_id)}}" alt="Karte {{$demaps[$x]->GeodbMaster()->name()}}" usemap="#map-{{$demaps[$x]->loc_id}}">
    - Bitte in eine for-schleife setzen oder das $x mit einer Zahl ersetzen.
3. In der Funktion /Karte/json/105 gebe ich noch die einzelnen Punkte als JSON zurück.   
    - &lt;map name="map-{{$demaps[$x]->loc_id}}" id="map-{{$demaps[$x]->loc_id}}">
    - ```
function gethtmlmap(id){
    $.ajax({
        url: "{{URL::asset("/karte/json")}}/" + id,
    }).done(function(data) {
        $("#map-" . id).html(""); 
        for(i=0; i < data.data.length; i++){
            area = $("#map-" + data.id).append("<area shape='circle' href='#' coords='" + data.data[i].x + "," + data.data[i].y + "," + (data.data[i].r + 10) + "' id='area" + data.data[i].x + data.data[i].y + data.data[i].r + "' data-id='" + i + "'>");
            $("#area" + data.data[i].x + data.data[i].y + data.data[i].r).mouseover(function (){
                UIkit.notify(data.data[$(this).data("id")].objects, {status:'info'});
            }); 
        }
    });
}
```
    - Einfach als Javascript gethtmlmap($demaps[$x]->loc_id) aufrufen um an das JSON für die Karte zu kommen.
    - UIkit.notify(....); setzt ein kleines notify am Kopf der Seite ab. (benötigt uikit)
    

### Daten abholen/Entfernung berechnen
Weg 1:
```
$geoloc = GeodbCoordinate::where("loc_id", $geotext->loc_id)->first();
$entrys = Location::select(\DB::raw("*, round(IFNULL((ACOS((SIN(RADIANS(" . $geoloc->lat ."))*SIN(RADIANS(lat))) + (COS(RADIANS(" . $geoloc->lat ."))*COS(RADIANS(lat))*COS(RADIANS(lon)-RADIANS(" . $geoloc->lon .")))) * 6371.110 ),0),2) AS distance"))->get();
```
Das Model Location hat die felder lat und lon gefüllt um nicht immer 100 MB an Daten zu durchsuchen.

Weg 2:
```
$augsburg = (new GeodbMaster())->searchByName("augsburg")->first();
$munich = (new GeodbMaster())->searchByPLZ(81929)->first();
$distanz = $augsburg->GeodbCoordinate()->calculatedistance($munich->GeodbCoordinate()->lon, $munich->GeodbCoordinate()->lat);
```

## Sonstiges
Um das ganze Skript in aktion zu erleben schaut es euch an:  
[![Thermen-Portal](http://www.thermen-portal.com/images/navbarlogo.png)](http://www.thermen-portal.com)

