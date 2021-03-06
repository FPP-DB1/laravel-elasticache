<?php

namespace Db1Fpp\Providers;

use Db1Fpp\Config\ConfigManager;
use Db1Fpp\Factories\MemcachedFactory;
use Illuminate\Cache\MemcachedStore;
use Illuminate\Cache\Repository;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Session\CacheBasedSessionHandler;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\ServiceProvider;

class ElastiCacheServiceProvider extends ServiceProvider
{

    public function boot()
    {
        $config        = $this->app->make('config');
        $configManager = new ConfigManager($config);

        Session::extend('elasticache', function () use ($configManager, $config) {
            $conn = $config->get('session.elasticache_connection');
            if (null === $conn) {
                $conn = 'default';
            }

            $elasticacheConfig = $configManager->get($conn);
            $memcachedInstance = MemcachedFactory::factory($elasticacheConfig);

            return new CacheBasedSessionHandler(new Repository(new MemcachedStore($memcachedInstance, $config->get('cache.prefix'))),
                $config->get('session.lifetime'));
        });

        Cache::extend('elasticache', function (Application $app, $driverConfig) use ($configManager, $config) {
            $elasticacheConfig = $configManager->get($driverConfig['connection']);
            $memcachedInstance = MemcachedFactory::factory($elasticacheConfig);

            return new Repository(new MemcachedStore($memcachedInstance, $config->get('cache.prefix')));
        });
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {

    }
}