<?php
/**
 * 控制器基类
 *
 * @Author huaixiu.zhen
 * http://litblc.com
 * User: z00455118
 * Date: 2018/9/3
 * Time: 11:31
 */

namespace App\Controllers;

use Library\Bootstrap;

class Controller
{
    protected $view;

    protected $path = ROOT . DS . 'app' . DS . 'Views';

    protected $cachePath = ROOT . DS . 'tmp' . DS . 'cache' . DS . 'view';

    /**
     * 加载twig模板 初始化视图
     *
     * Controller constructor.
     */
    public function __construct()
    {
//        $loader = new \Twig_Loader_Filesystem($this->path);
//
//        $this->view = new \Twig_Environment($loader, array(
//            'cache' => $this->cachePath,
//            'debug' => Bootstrap::$config['app_debug']
//        ));
    }

    /**
     * 统一输入
     *
     * @param $key
     * @param string $default
     * @return mixed|string
     */
    protected function request($key, $default = '')
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
    protected function jsonResponse(array $data, $success = true, $message = '')
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
