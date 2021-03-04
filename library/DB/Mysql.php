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

class Mysql
{
    // 测试单例运行次数
    public static $counter = 0;

    private static $instance = null;

    private $conn;

    /**
     * 创建链接
     *
     * Mysql constructor.
     * @param $config
     */
    private function __construct($config)
    {
        $this->conn = new \mysqli($config['host'], $config['user'], $config['password'], $config['database'], $config['port']);
        $this->conn->query('set names utf8');
        self::$counter += 1;
    }

    /**
     * @Author huaixiu.zhen
     * http://litblc.com
     *
     * @return string
     */
    private function __clone()
    {
        return 'no access';
    }

    /**
     * @Author huaixiu.zhen
     * http://litblc.com
     *
     * @param $config
     *
     * @return Mysql|null
     */
    public static function getInstance($config)
    {
        if (!self::$instance) {
            self::$instance = new Mysql($config);
        }
        return self::$instance;
    }

    /**
     * 暴露query原生方法 可以在query后直接使用原生mysqli方法
     * 如 $res = $db->query($sql)->fetch_assoc();
     *
     * @Author huaixiu.zhen
     * http://litblc.com
     *
     * @param $sql
     *
     * @return array|bool
     */
    public function query($sql)
    {
        $result = $this->conn->query($sql);
        return $result;
    }

    /**
     * 取出第一行
     *
     * @Author huaixiu.zhen
     * http://litblc.com
     *
     * @param $sql
     *
     * @return array | string
     */
    public function fetchFirst($sql)
    {
        $result = $this->query($sql);
        if ($result) {
            if ($result->num_rows) {
                $res = $result->fetch_assoc();    // $res = $result->fetch_object();
            } else {
                $res = [];
            }
        } else {
            $res = $this->conn->error;
        }
        return $res;
    }

    /**
     * 取出所有行
     *
     * @Author huaixiu.zhen
     * http://litblc.com
     *
     * @param $sql
     *
     * @return array|string
     */
    public function fetchAll($sql)
    {
        $rows = [];
        $result = $this->query($sql);
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $rows[] = $row;
            }
        } else {
            return $this->conn->error;
        }

        return $rows;
    }
}