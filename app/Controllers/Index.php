<?php
/**
 * 示例控制器（默认控制器）
 *
 * @Author huaixiu.zhen
 * http://litblc.com
 * User: z00455118
 * Date: 2018/8/30
 * Time: 15:50
 */

namespace App\Controllers;

class Index extends Controller
{
    /**
     * @Author huaixiu.zhen
     * http://litblc.com
     *
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public function index()
    {
        // ?controller=index&action=index
        $data = ['data' => 'welcome to fakePHP'];
        echo $this->view->render('welcome.html', $data);
    }

}
