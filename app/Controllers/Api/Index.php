<?php
/**
 * 多麦
 * https://xxxx/ganfanzu-api/public?module=api&controller=Index&action=getList
 *
 * @Author huaixiu.zhen
 * http://litblc.com
 * User: z00455118
 * Date: 2018/8/30
 */

namespace App\Controllers\Api;

use Duomai\CpsClient\Client;
use App\Controllers\Controller;
use Library\Bootstrap;

class Index extends Controller
{
    private $client;

    private $config;


    public function __construct()
    {
        error_reporting(0);

        $this->config = Bootstrap::$config['duomai'];

        // 初始化 配置信息
        $config = [
            'host' => $this->config['host'],
            'auth' => [
                'app_key' => $this->config['appKey'],
                'app_secret' => $this->config['appSecret'],
            ]
        ];
        if (!$this->client) {
            $this->client = new Client($config);
        }
    }

    /**
     * 获取商品列表
     */
    public function getList()
    {
        $platform = $_POST['platform'];
        $query = $_POST['query'];

        if (!in_array($platform, ['jd', 'pdd'])) {
            return $this->jsonResponse([], false, 'no support this platform');
        }

        try {
            $api = "cps-mesh.cpslink.{$platform}.products.get";

            $data = $this->client->Request($api, [
                'query' => $query
            ]);

            return $this->jsonResponse($data);

        } catch (\Exception $exception) {
            return $this->jsonResponse([], false, 'network error');
        }
    }

    /**
     * 根据item_id获取详情（与list中的一样）
     */
    public function getDetail()
    {
        $platform = $_POST['platform'];
        $query = $_POST['item_id'];

        if (!in_array($platform, ['jd', 'pdd'])) {
            return $this->jsonResponse([], false, 'no support this platform');
        }

        try {
            $api = "cps-mesh.cpslink.{$platform}.products.detail";

            if (!$query) {
                throw new \Exception('item_id required');
            }

            $data = $this->client->Request($api, [
                'id' => $query
            ]);

            return $this->jsonResponse($data);
        } catch (\Exception $exception) {
            return $this->jsonResponse([], false, 'network error');
        }
    }

    /**
     * 转链 返回小程序url
     */
    public function getLink()
    {
        $query = $_POST['item_url'];

        try {
            $api = 'cps-mesh.cpslink.links.post';

            if (!$query) {
                throw new \Exception('item_url required');
            }

            $data = $this->client->Request($api, [
                'site_id' => $this->config['siteId'],
                'url' => $query,    //'https://item.jd.com/100014809454.html'
                'original' => 1
            ]);

            return $this->jsonResponse($data);

        } catch (\Exception $exception) {
            return $this->jsonResponse([], false, 'network error');
        }
    }

    /**
     * url链接解析商品与计划
     */
    private function linkHandle($itemUrl)
    {
        try {
            $api = 'cps-mesh.cpslink.links.put';

            $data = $this->client->Request($api, [
                'url' => $itemUrl
            ]);

            return $data;
        } catch (\Exception $exception) {
            return $this->jsonResponse([], false, 'network error');
        }
    }

    private function jsonResponse(array $data, $success = true, $message = '')
    {
        header('Content-Type:application/json');

        $data['code'] = 0;
        // 失败
        if ($success === false) {
            $data['code'] = -1;
            $data['message'] = $message;
        }
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
    }
}
