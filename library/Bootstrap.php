<?php
/**
 * @Author huaixiu.zhen
 * http://litblc.com
 * User: z00455118
 * Date: 2018/8/30
 */

namespace Library;

class Bootstrap
{
    public static $config = [];

    private static $controller = 'Index';

    private static $action = 'index';

    /**
     * 加载配置文件
     *
     * @Author huaixiu.zhen
     * http://litblc.com
     */
    private static function setConfig()
    {
        self::$config = require_once(ROOT . DS . 'config' . DS . 'config.php');
    }

    /**
     * 显示错误开关
     *
     * @Author huaixiu.zhen
     * http://litblc.com
     */
    private static function displayErrors()
    {
        if (self::$config['app_debug'] === false) {
            ini_set('display_errors', 'off');
        }
    }

    /**
     * 路由解析
     * 支持模型/控制器/方法名
     *
     * @Author huaixiu.zhen
     * http://litblc.com
     */
    private static function route()
    {
        // 使用path_info模式必须先开启path_info
        if (isset($_SERVER['REDIRECT_PATH_INFO']) || isset($_SERVER['PATH_INFO'])) {
            $pathInfo = isset($_SERVER['PATH_INFO']) ? trim($_SERVER['PATH_INFO'], '/') : trim($_SERVER['REDIRECT_PATH_INFO'], '/');
            $baseSpace = 'App\Controllers\\';
            $controller = $baseSpace . 'Index';
            $action = 'index';print_r($pathInfo);echo 'ok';
            if (mb_strlen($pathInfo)) {
                $route = explode('/', $pathInfo);
                if (count($route) == 3) {
                    $controller = $baseSpace . ucfirst(strtolower($route[0])) . '\\' . ucfirst(strtolower($route[1]));
                    $action = isset($route[2]) ? (strtolower($route[2])) : 'index';
                } else {
                    $controller = isset($route[0]) ? $baseSpace . ucfirst(strtolower($route[0])) : 'Index';
                    $action = isset($route[1]) ? (strtolower($route[1])) : 'index';
                }
            }
            self::$controller = $controller;
            self::$action = $action;
        } else {
            $baseSpace = 'App\Controllers\\';
            $module = (isset($_REQUEST['module']) && $_REQUEST['module']) ?
                $baseSpace . ucfirst(strtolower($_REQUEST['module'])) :
                '';
            if ($module) {
                $controller = (isset($_REQUEST['controller']) && $_REQUEST['controller']) ?
                    $module . '\\' . ucfirst(strtolower($_REQUEST['controller'])) :
                    $module . '\\Index';
            } else {
                $controller = (isset($_REQUEST['controller']) && $_REQUEST['controller']) ?
                    $baseSpace . ucfirst(strtolower($_REQUEST['controller'])) :
                    $baseSpace . 'Index';
            }
            $action = (isset($_REQUEST['action']) && $_REQUEST['action']) ?
                strtolower($_REQUEST['action']) :
                'index';
            self::$controller = $controller;
            self::$action = $action;
        }
    }

    /**
     * 路由调度分发
     *
     * @Author huaixiu.zhen
     * http://litblc.com
     */
    private static function dispatch()
    {
        $controller = self::$controller;
        $action = self::$action;
        $object = new $controller();
        $object->$action();
    }

    /**
     * 自动加载
     *
     * @Author huaixiu.zhen
     * http://litblc.com
     */
    private static function splAutoloadRegister()
    {
        spl_autoload_register('self::autoload');
    }

    /**
     * @Author huaixiu.zhen
     * http://litblc.com
     *
     * @param $name
     */
    private static function autoload($name)
    {
        $fileName = ROOT . DS .lcfirst($name) . '.php';
        if (file_exists($fileName)) {
            include_once ($fileName);
        }
    }

    /**
     * 框架启动程序
     *
     * @Author huaixiu.zhen
     * http://litblc.com
     */
    public static function run()
    {
        self::setConfig();
        self::displayErrors();
        self::splAutoloadRegister();
        session_start();
        self::route();
        self::dispatch();
    }
}
