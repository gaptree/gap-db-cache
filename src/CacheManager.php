<?php
namespace Gap\Cache;

use Symfony\Component\Cache\Simple\RedisCache;
use Symfony\Component\Cache\Adapter\RedisAdapter;

class CacheManager
{
    protected $cnns;
    protected $optsSet;

    public function __construct($optsSet)
    {
        $this->optsSet = $optsSet;
    }

    public function connect($name)
    {
        if (isset($this->cnns[$name])) {
            return $this->cnns[$name];
        }

        if (!$opts = $this->optsSet[$name] ?? null) {
            throw new \Exception("cannot find config for cache [$name]");
        }

        if ('default' === ($opts['adapter'] ?? 'default')) {
            $redis = $this->createRedis($opts);
            return $redis;
        }

        if ('PSR16' === ($opts['adapter'] ?? 'default')) {
            if ('redis'===($opts['driver'] ?? 'redis')) {
                $redis = $this->createRedis($opts);
                $cache = new RedisCache($redis);
                
                return $cache;
            }
            $className = $this->driver2Psr16Class($opts['driver']);
            $class = new \ReflectionClass($className);
            $cache = $class->newInstanceArgs();
     
            return $cache;
        }

        if ('PSR6' === ($opts['adapter'] ?? 'default')) {
            if ('redis'===($opts['driver'] ?? 'redis')) {
                $redis = $this->createRedis($opts);
                $cache = new RedisCache($redis);
                
                return $cache;
            }

            $className = $this->driver2Psr6Class($opts['driver']);
            $class = new \ReflectionClass($className);
            $cache = $class->newInstanceArgs();

            return $cache;
        }
    }

    protected function driver2Psr16Class(string $adapter)
    {
        $class = 'Symfony\Component\Cache\Simple\\'.ucfirst($adapter).'Cache';
        if (class_exists($class)) {
            return $class;
        }

        throw new \Exception("cannot find cache adapter class");
    }

    protected function driver2Psr6Class(string $adapter)
    {
        $class = 'Symfony\Component\Cache\Adapter\\'.ucfirst($adapter).'Cache';
        if (class_exists($class)) {
            return $class;
        }

        throw new \Exception("cannot find cache adapter class");
    }

    protected function createRedis($opts): \Redis
    {
        $host = $opts['host'] ?? '127.0.0.1';
        $port = $opts['port'] ?? 6379;
        $database = $opts['database'] ??  0;
        $redis = new \Redis();
        $redis->connect($host, $port);
        $redis->select($database);

        return $redis;
    }
}
