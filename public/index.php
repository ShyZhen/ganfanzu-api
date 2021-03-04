<?php
/**
 * @Author huaixiu.zhen
 * http://litblc.com
 * User: z00455118
 * Date: 2018/8/30
 */

define('DS', DIRECTORY_SEPARATOR);
define('ROOT', dirname(__DIR__));

require_once(ROOT . DS . 'vendor' . DS . 'autoload.php');
require_once(ROOT . DS . 'library' . DS . 'Bootstrap.php');

Library\Bootstrap::run();