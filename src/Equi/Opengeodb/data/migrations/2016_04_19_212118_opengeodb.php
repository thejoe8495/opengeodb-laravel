<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Opengeodb extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('geodb_type_names', function (Blueprint $table) {
            $table->integer('type_id');
            $table->string('type_locale', 5);
            $table->string('name');
            $table->unique(['type_id', 'type_locale']);
        });         
        Schema::create('geodb_locations', function (Blueprint $table) {
            $table->integer('loc_id');
            $table->primary('loc_id');
            $table->integer('loc_type');
        });
        
        Schema::create('geodb_hierarchies', function (Blueprint $table) {
            $table->integer('loc_id');
            $table->foreign('loc_id')->references('loc_id')->on('geodb_locations');
            $table->integer('level');
            $table->integer('id_lvl1');
            $table->integer('id_lvl2')->nullable();
            $table->integer('id_lvl3')->nullable();
            $table->integer('id_lvl4')->nullable();
            $table->integer('id_lvl5')->nullable();
            $table->integer('id_lvl6')->nullable();
            $table->integer('id_lvl7')->nullable();
            $table->integer('id_lvl8')->nullable();
            $table->integer('id_lvl9')->nullable();
            $table->date('valid_since')->nullable();
            $table->integer('date_type_since')->nullable();
            $table->date('valid_until');
            $table->integer('date_type_until'); 
        }); 
        
        Schema::create('geodb_coordinates', function (Blueprint $table) {
            $table->integer('loc_id');
            $table->foreign('loc_id')->references('loc_id')->on('geodb_locations');
            $table->integer('coord_type');
            $table->double('lat')->nullable();
            $table->double('lon')->nullable();
            $table->integer('coord_subtype')->nullable();
            $table->date('valid_since')->nullable();
            $table->integer('date_type_since')->nullable();
            $table->date('valid_until');
            $table->integer('date_type_until');  
        });
        
        Schema::create('geodb_textdata', function (Blueprint $table) {
            $table->integer('loc_id');
            $table->foreign('loc_id')->references('loc_id')->on('geodb_locations');
            $table->integer('text_type');
            $table->string('text_val');
            $table->string('text_locale', 5)->nullable();
            $table->boolean('is_native_lang')->nullable();
            $table->boolean('is_default_name')->nullable();    
            
            $table->date('valid_since')->nullable();
            $table->integer('date_type_since')->nullable();
            $table->date('valid_until');
            $table->integer('date_type_until');
        }); 
         
        Schema::create('geodb_intdata', function (Blueprint $table) {
            $table->integer('loc_id');
            $table->foreign('loc_id')->references('loc_id')->on('geodb_locations');
            $table->integer('int_type');
            $table->bigInteger('int_val');
            $table->date('valid_since')->nullable();
            $table->integer('date_type_since')->nullable();
            $table->date('valid_until');
            $table->integer('date_type_until');  
        });  
               
        Schema::create('geodb_floatdata', function (Blueprint $table) {
            $table->integer('loc_id');
            $table->foreign('loc_id')->references('loc_id')->on('geodb_locations');
            $table->integer('float_type');
            $table->double('float_val'); 
            $table->date('valid_since')->nullable();
            $table->integer('date_type_since')->nullable();
            $table->date('valid_until');
            $table->integer('date_type_until');  
        }); 
                       
        Schema::create('geodb_changelog', function (Blueprint $table) {
            $table->integer('id');
            $table->primary('id');
            $table->date('datum'); 
            $table->string('beschreibung');
            $table->string('autor');
            $table->string('version'); 
        });
                               
        Schema::create('geodb_mapcoords', function (Blueprint $table) {  
            $table->integer('loc_id');
            $table->foreign('loc_id')->references('loc_id')->on('geodb_locations');
            $table->double('fromlat');
            $table->double('fromlon');
            $table->double('tolat');
            $table->double('tolon');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('geodb_mapcoords');
        Schema::drop('geodb_changelog');
        Schema::drop('geodb_floatdata');
        Schema::drop('geodb_intdata');
        Schema::drop('geodb_textdata');
        Schema::drop('geodb_coordinates');
        Schema::drop('geodb_hierarchies');
        Schema::drop('geodb_locations');
        Schema::drop('geodb_type_names');
    }
}
