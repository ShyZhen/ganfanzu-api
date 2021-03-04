<?php
/**
 * 辅助函数
 *
 * @Author huaixiu.zhen
 * http://litblc.com
 * User: z00455118
 * Date: 2018/11/29
 * Time: 13:45
 */

namespace Library\Common;

class Helper
{
    /**
     * 对字符串或者数组进行转义(过滤注入)
     *
     * @Author huaixiu.zhen
     * http://litblc.com
     *
     * @param $data string|array 字符串或者数组
     *
     * @return string|array 不改变原数据格式
     */
    public static function filterInjection($data)
    {
        if (is_array($data)) {
            array_walk_recursive($data, 'self::addslashes');
        } else if (is_string($data)) {
            $data = addslashes(htmlspecialchars(trim($data)));
        }

        return $data;
    }

    /**
     * 函数 filterInjection 中 array_walk_recursive 的回调函数，对字符串进行过滤
     *
     * @Author huaixiu.zhen
     * http://litblc.com
     *
     * @param $str
     *
     * @return string
     */
    private static function addslashes(&$str)
    {
        if (is_string($str)) {
            $str = addslashes(htmlspecialchars(trim($str)));
        }

        return $str;
    }

    /**
     * 将数组转换成int,数组里的数字不能打大于63
     * params array 包含id的一维数组
     *
     * @Author huaixiu.zhen
     * http://litblc.com
     *
     * @param array $arr
     *
     * @return int
     */
    public static function shiftInt($arr = [])
    {
        $res = 0;
        foreach ($arr as $temp) {
            $temp = (int) ($temp);
            if ($temp > 63 || $temp == 0) {
                return 0;
            }
            $res += (1 << ($temp - 1));
        }

        return $res;
    }

    /**
     * 将处理过的数字转换成单个id的数组
     * params int 由shiftInt生成的数字
     *
     * @Author huaixiu.zhen
     * http://litblc.com
     *
     * @param int $int
     *
     * @return array
     */
    public static function shiftArray($int = 0)
    {
        $int = (int) $int;
        if (empty($int) || $int <= 0) {
            return [];
        }
        $fix = 1;
        $res = [];
        while ($int && $fix < 100) {
            if ($int & 1) {
                $res[] = $fix;
            }
            $int = $int >> 1;
            ++ $fix;
        }

        return $res;
    }

    /**
     * 统一输入
     *
     * @param $key
     * @param string $default
     * @return mixed|string
     */
    public static function request($key, $default = '')
    {
        $raw = @file_get_contents('php://input');
        $bodyData = json_decode($raw, true);

        $data = $default;
        if (key_exists($key, $bodyData)) {
            $data = $bodyData[$key];
        }

        return $data;
    }

    /**
     * 统一输出
     *
     * @param array $data
     * @param bool $success
     * @param string $message
     */
    public static function jsonResponse(array $data, $success = true, $message = '')
    {
        header('Content-Type:application/json');
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Request-Methods:GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers:x-requested-with,content-type,test-token,test-sessid');

        $data['code'] = 0;
        // 失败
        if ($success === false) {
            $data['code'] = -1;
            $data['message'] = $message;
        }
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
    }
}
