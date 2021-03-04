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
}
