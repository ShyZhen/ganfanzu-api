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
     */
    public function index()
    {
        echo '?controller=index&action=index';
    }

}
