<?php
/**
 * 数据库类
 *
 * @Author huaixiu.zhen
 * http://litblc.com
 * User: z00455118
 * Date: 2018/11/24
 * Time: 14:11
 */

namespace Library\DB;

use Library\Bootstrap;
use Predis\Client;

class Redis
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
