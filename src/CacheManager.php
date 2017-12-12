<?php
namespace Gap\Cache;

use Symfony\Component\Cache\Simple\RedisCache;

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
        $adapter = $opts['adapter'] ?? 'redis';

        if ('redis' === $adapter) {
            $host = $opts['host'] ?? '127.0.0.1';
            $port = $opts['port'] ?? 6379;
            $database = $opts['database']??  0;
            $client = new Client("redis://$host:$post");
            $client->select($database);
            $cache = new RedisCache($client);

            return $cache;
        }

        $class = adapter2Class($adapter);
        $cache = new ReflectionClass($class);
        
        return $cache;
    }

    protected function adapter2Class(string $adapter)
    {
        $class = 'Symfony\Component\Cache\Simple\\'.ucfirst($adapter).'Cache';
        if(class_exists($class)){
            return $class;
        }

        throw new \Exception("cannot find cache adapter class");
    } 
}
