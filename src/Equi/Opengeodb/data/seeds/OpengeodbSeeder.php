<?php

use Illuminate\Database\Seeder;
use Symfony\Component\Console\Output\ConsoleOutput;

class OpengeodbSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->importfile("AT.sql");
        $this->importfile("AThier.sql");
        $this->importfile("BE.sql");
        $this->importfile("BEhier.sql");
        $this->importfile("LI.sql");
        $this->importfile("LIhier.sql");
        $this->importfile("CH.sql");
        $this->importfile("CHhier.sql");
        $this->importfile("DE.sql");
        $this->importfile("DEhier.sql");
        $this->importfile("opengeodb-end.sql");
        $this->importfile("opengeodb_hier.sql");
        $this->importfile("extra.sql");
        DB::table('geodb_textdata')->where("date_type_until","0")->delete();
        $this->importfile("changes.sql", true);
        
        $output = new ConsoleOutput();
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
        DB::table('geodb_mapcoords')->insert(["loc_id" => "105", "fromlat" => 5.5, "fromlon" => 47, "tolat" => 15.5, "tolon" => 55]);
        DB::table('geodb_mapcoords')->insert(["loc_id" => "108", "fromlat" => 11, "fromlon" => 51, "tolat" => 15, "tolon" => 53.5]);
        DB::table('geodb_mapcoords')->insert(["loc_id" => "110", "fromlat" => 7, "fromlon" => 47, "tolat" => 11, "tolon" => 50]);
        DB::table('geodb_mapcoords')->insert(["loc_id" => "111", "fromlat" => 8, "fromlon" => 47, "tolat" => 14.5, "tolon" => 51]);
        DB::table('geodb_mapcoords')->insert(["loc_id" => "113", "fromlat" => 7.5, "fromlon" => 49, "tolat" => 10.5, "tolon" => 52]);
        DB::table('geodb_mapcoords')->insert(["loc_id" => "115", "fromlat" => 10, "fromlon" => 53, "tolat" => 14.5, "tolon" => 55]);
        DB::table('geodb_mapcoords')->insert(["loc_id" => "116", "fromlat" => 5.5, "fromlon" => 51, "tolat" => 12, "tolon" => 55]);
        DB::table('geodb_mapcoords')->insert(["loc_id" => "117", "fromlat" => 5.5, "fromlon" => 50, "tolat" => 10, "tolon" => 53]);
        DB::table('geodb_mapcoords')->insert(["loc_id" => "118", "fromlat" => 5.5, "fromlon" => 48.5, "tolat" => 9, "tolon" => 51.5]);
        DB::table('geodb_mapcoords')->insert(["loc_id" => "119", "fromlat" => 7, "fromlon" => 53, "tolat" => 12, "tolon" => 55]);
        DB::table('geodb_mapcoords')->insert(["loc_id" => "120", "fromlat" => 5.5, "fromlon" => 49, "tolat" => 8, "tolon" => 50]);
        DB::table('geodb_mapcoords')->insert(["loc_id" => "121", "fromlat" => 11, "fromlon" => 50, "tolat" => 15.5, "tolon" => 52]);
        DB::table('geodb_mapcoords')->insert(["loc_id" => "122", "fromlat" => 10, "fromlon" => 50.5, "tolat" => 13.5, "tolon" => 53.5]);
        DB::table('geodb_mapcoords')->insert(["loc_id" => "123", "fromlat" => 9.5, "fromlon" => 50, "tolat" => 13, "tolon" => 52]);
        DB::table('geodb_mapcoords')->insert(["loc_id" => "106", "fromlat" => 9, "fromlon" => 46, "tolat" => 18, "tolon" => 50]);
        DB::table('geodb_mapcoords')->insert(["loc_id" => "107", "fromlat" => 5, "fromlon" => 45.5, "tolat" => 11, "tolon" => 48]);
        Schema::table('geodb_mapcoords', function ($table) {                      
            $table->index('loc_id');
        });
    }
    
    public function importfile($filename, $ignoreerror = false){
        if (!Illuminate\Support\Facades\Storage::exists(Config::get('opengeodb.storageopengodbsql')."/" .$filename))
            return;
        $output = new ConsoleOutput(); 
        $sql = Illuminate\Support\Facades\Storage::get(Config::get('opengeodb.storageopengodbsql'). "/" .$filename);
        
        $statements = explode("\n", $sql);
        $lines = count($statements);
        $output->writeln('Starte Datei:' . $filename. " Zeilen: ". $lines);
        DB::beginTransaction();
        for($i=0; $i<$lines; $i++)
        {
            //if (is_int($i/ 1000)) $output->writeln(date("H:i:s") .': Run '.$i.' of ' .$lines);
            $line = preg_replace('!/\*.*?\*/!s', '', $statements[$i]);
            if (!empty(str_replace("\r", "", $line)) && $line != "BEGIN;" && $line != "COMMIT;" && strpos($line, "create index") === false){
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
