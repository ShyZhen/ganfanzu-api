<?php
/**
 * 模型基类
 *
 * @Author huaixiu.zhen
 * http://litblc.com
 * User: z00455118
 * Date: 2018/11/26
 * Time: 17:37
 */

namespace App\Models;

use Library\Bootstrap;
use Library\DB\Mysql;

class Model
{
    public static $DB;

    /**
     * 初始化数据库链接
     *
     * @Author huaixiu.zhen
     * http://litblc.com
     *
     * @return Mysql|null
     */
    public static function DB()
    {
        $config = Bootstrap::$config['mysql'];
        self::$DB = Mysql::getInstance($config);
        return self::$DB;
    }
}