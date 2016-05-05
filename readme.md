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

### Konfiguration opengeodb.php

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
    
    'radiusdata' => [
        1 => 2,
        2 => 3,
        3 => 4,
        4 => 5,
        5 => 6,
        6 => 4
    ]
    
];
```

### Datenbanken erstellen und füllen
```
php artisan migrate --seed
```

## Einbinden/Benutzen


## Zusätzliche Daten

