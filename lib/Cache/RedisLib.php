<?php
/**
 * redisLib
 * @author DELL
 * 2021/3/18 14:21
 **/

namespace Lib\Cache;


use Library\Bootstrap;
use Predis\Client;

class RedisLib
{
    private $drive = 'redis';

    private $config;

    public $redis;

    public function __construct()
    {
        if (!$this->redis) {
            $this->config = Bootstrap::$config['cache'][$this->drive];

            $param = [
                'scheme' => 'tcp',
                'host'   => $this->config['host'],
                'port'   => $this->config['port'],
            ];
            $this->redis = new Client($param);
        }
    }
}
