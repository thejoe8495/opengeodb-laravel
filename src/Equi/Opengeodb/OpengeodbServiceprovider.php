<?php
namespace Equi\Opengeodb;
/**
 * 
 * @author kora jai <kora.jayaram@gmail>
 */
use Illuminate\Support\ServiceProvider;
use Illuminate\Routing\Router;
class OpengeodbServiceprovider extends ServiceProvider{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;
    public function boot()
    {
        // this  for conig
        $this->publishes([
              __DIR__.'/data/config/opengeodb.php' => config_path('opengeodb.php'),
        ], 'config');
        $this->publishes([
              __DIR__.'/data/maps/' => storage_path('app/opengeodb/e00/'),
        ], 'storage');
        $this->publishes([
              __DIR__.'/data/migrations/2016_04_19_212118_opengeodb.php' => database_path('migrations/2016_04_19_212118_opengeodb.php'),
              __DIR__.'/data/seeds/OpengeodbSeeder.php' => database_path('seeds/OpengeodbSeeder.php'),
        ], 'database');
    }
    
    public function register()
    { 
        config([
                'config/opengeodb.php',
        ]);
    }
}