<?php

use Illuminate\Database\Seeder;
use Symfony\Component\Console\Output\ConsoleOutput;
use Illuminate\Support\Facades\Storage;

class OpengeodbSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $output = new ConsoleOutput(); 
        $output->writeln('Starting');
        $sqlfiles = ["AT.sql", "AThier.sql","BE.sql","BEhier.sql","LI.sql","LIhier.sql","CH.sql","CHhier.sql","DE.sql","DEhier.sql"];
        foreach($sqlfiles as $filename){
            if (Storage::exists(Config::get('opengeodb.storageopengodbsql'). "/" .$filename))  
                $this->importfile($filename); 
        } 
        DB::table('geodb_textdata')->where("date_type_until","0")->delete();
        
        $sqlfiles = ["opengeodb-end.sql","opengeodb_hier.sql","extra.sql","changes.sql"];
        foreach($sqlfiles as $filename){
            if (Storage::exists(Config::get('opengeodb.storageopengodbsql'). "/" .$filename))  
                $this->importfile($filename, true); 
        }
        
        $output->writeln('Creating Index');
        Schema::table('geodb_type_names', function ($table) {                      
            $table->index('type_id');
            $table->index('type_locale');
            $table->index('name');
        });
            Schema::table('geodb_locations', function ($table) {                      
            $table->index('loc_type');
        });
        Schema::table('geodb_coordinates', function ($table) {                      
            $table->index('loc_id');
            $table->index('lon');
            $table->index('lat');
            $table->index('coord_type');
            $table->index('coord_subtype');
            $table->index('valid_since');
            $table->index('valid_until');
        });
        Schema::table('geodb_hierarchies', function ($table) {                      
            $table->index('loc_id');
            $table->index('level');
            $table->index('id_lvl1');
            $table->index('id_lvl2');
            $table->index('id_lvl3');
            $table->index('id_lvl4');
            $table->index('id_lvl5');
            $table->index('id_lvl6');
            $table->index('id_lvl7');
            $table->index('id_lvl8');
            $table->index('id_lvl9');
            $table->index('valid_since');
            $table->index('valid_until');
        }); 
        Schema::table('geodb_textdata', function ($table) {                      
            $table->index('loc_id');
            $table->index('text_val');
            $table->index('text_type');
            $table->index('text_locale');
            $table->index('is_native_lang');
            $table->index('is_default_name');
            $table->index('valid_since');
            $table->index('valid_until');
        });
        Schema::table('geodb_intdata', function ($table) {                      
            $table->index('loc_id');
            $table->index('int_val');
            $table->index('int_type');
            $table->index('valid_since');
            $table->index('valid_until');
        });
        Schema::table('geodb_floatdata', function ($table) {                      
            $table->index('loc_id');
            $table->index('float_val');
            $table->index('float_type');
            $table->index('valid_since');
            $table->index('valid_until');
        });
        $this->insertmapcoords(["loc_id" => "105", "fromlat" => 5.5, "fromlon" => 47, "tolat" => 15.5, "tolon" => 55]);
        $this->insertmapcoords(["loc_id" => "108", "fromlat" => 11, "fromlon" => 51, "tolat" => 15, "tolon" => 53.5]);
        $this->insertmapcoords(["loc_id" => "110", "fromlat" => 7, "fromlon" => 47, "tolat" => 11, "tolon" => 50]);
        $this->insertmapcoords(["loc_id" => "111", "fromlat" => 8, "fromlon" => 47, "tolat" => 14.5, "tolon" => 51]);
        $this->insertmapcoords(["loc_id" => "113", "fromlat" => 7.5, "fromlon" => 49, "tolat" => 10.5, "tolon" => 52]);
        $this->insertmapcoords(["loc_id" => "115", "fromlat" => 10, "fromlon" => 53, "tolat" => 14.5, "tolon" => 55]);
        $this->insertmapcoords(["loc_id" => "116", "fromlat" => 5.5, "fromlon" => 51, "tolat" => 12, "tolon" => 55]);
        $this->insertmapcoords(["loc_id" => "117", "fromlat" => 5.5, "fromlon" => 50, "tolat" => 10, "tolon" => 53]);
        $this->insertmapcoords(["loc_id" => "118", "fromlat" => 5.5, "fromlon" => 48.5, "tolat" => 9, "tolon" => 51.5]);
        $this->insertmapcoords(["loc_id" => "119", "fromlat" => 7, "fromlon" => 53, "tolat" => 12, "tolon" => 55]);
        $this->insertmapcoords(["loc_id" => "120", "fromlat" => 5.5, "fromlon" => 49, "tolat" => 8, "tolon" => 50]);
        $this->insertmapcoords(["loc_id" => "121", "fromlat" => 11, "fromlon" => 50, "tolat" => 15.5, "tolon" => 52]);
        $this->insertmapcoords(["loc_id" => "122", "fromlat" => 10, "fromlon" => 50.5, "tolat" => 13.5, "tolon" => 53.5]);
        $this->insertmapcoords(["loc_id" => "123", "fromlat" => 9.5, "fromlon" => 50, "tolat" => 13, "tolon" => 52]);
        $this->insertmapcoords(["loc_id" => "106", "fromlat" => 9, "fromlon" => 46, "tolat" => 18, "tolon" => 50]);
        $this->insertmapcoords(["loc_id" => "107", "fromlat" => 5, "fromlon" => 45.5, "tolat" => 11, "tolon" => 48]);
        Schema::table('geodb_mapcoords', function ($table) {                      
            $table->index('loc_id');
        });
    }
    
    public function insertmapcoords($fields){
        try{
            DB::table('geodb_mapcoords')->insert($fields);
        } catch (\Exception $ex){
        }
    }
    
    public function importfile($filename, $ignoreerror = false){
        $output = new ConsoleOutput(); 
        $sql = Storage::get(Config::get('opengeodb.storageopengodbsql'). "/" .$filename);
        
        $statements = explode("\n", $sql);
        $output->writeln('Starte Datei:' . Config::get('opengeodb.storageopengodbsql'). "/" .$filename . " Zeilen: ");
        $lines = count($statements);
        $output->writeln('Starte Datei:' . $filename. " Zeilen: ". $lines);
        DB::beginTransaction();
        for($i=0; $i<$lines; $i++)
        { 
            $line = preg_replace('!/\*.*?\*/!s', '', $statements[$i]);
            //$output->writeln('Zeile:' . $line);
            if (!empty(str_replace("\r", "", $line)) && $line != "BEGIN;" && $line != "COMMIT;" && strpos($line, "create index") === false){
                if (strpos($line,"geodb_floatdata")) $line = str_replace(",0,", ",null,", $line);
                if ($ignoreerror){
                    try{
                        DB::statement($line);
                    } catch (\Exception $ex){
                    }
                } else
                    DB::statement($line);
            } 
        }         
        DB::commit();
        $output->writeln('Datei:' . $filename. " fertig");
    }
}
