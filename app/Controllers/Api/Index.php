<?php
/**
 * 多麦
 * http://ganfanzu-test.com:81/?module=Api&controller=Index&action=getList
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
        $page = (int) $_POST['page'] ?: 1;

        if (!in_array($platform, ['jd', 'pdd'])) {
            return $this->jsonResponse([], false, 'no support this platform');
        }

        try {
            $api = "cps-mesh.cpslink.{$platform}.products.get";

            $data = $this->client->Request($api, [
                'query' => $query,
                'page' => $page,
                'order_field' => 'volume ',  // 排序字段 commission_rate 佣金比例 price价格 volume 销量
           ]);

            return $this->jsonResponse($data);

        } catch (\Exception $exception) {
            return $this->jsonResponse([], false, 'network error');
        }
    }

    /**
     * 转链 返回小程序url
     *
     * @return void
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
                'url' => $query,                             // 形如 'https://item.jd.com/100014809454.html'
                'original' => 1
            ]);

            return $this->jsonResponse($data);

        } catch (\Exception $exception) {
            return $this->jsonResponse([], false, 'network error');
        }
    }

    /**
     * 使用getLink生成后，直接返回给前端（写可以写接口调用，但是循环调用性能差，条目不宜过多）
     * http://ganfanzu-test.com:81/?module=Api&controller=Index&action=getLink
     */
    public function getCustomProduct()
    {
        $itemUrls = [
            [
                'item_url' => 'https://item.jd.com/100008920641.html',
                'short_url' => 'https://u.jd.com/u9tjDSr',
                'product_title' => '卫龙魔芋爽 辣条 休闲零食 办公室零食 香辣素毛肚魔芋爽大礼包600g/袋装',
                'product_main_picture' => 'https://img14.360buyimg.com/pop/jfs/t1/160412/29/3705/134092/6006ab1aE9214ba99/edc6a1f77440f082.jpg',
                'product_original_price' => '39.9',
                'product_coupon_after_price' => '38.8',
                'wx_appid' => 'wx91d27dbf599dff74',
                'wx_path' => 'pages/union/proxy/proxy?spreadUrl=https%3A%2F%2Funion-click.jd.com%2Fjdc%3Fe%3D16282%26p%3DAyIGZRtYEAYVBVYbXx0yEgZUGlocChEGUh5bJUZNXwtEa0xHV0YXEEULWldTCQQAQB1AWQkFWxQDEwZcE1gUBRcHSkIeSV8iQDdhPEt%252FZEQ3YyVlUUFaL0wsVnBFUVkXaxIHFQFcEl4RMhIAVhJYJQAaAVwbWRQBIgdUKxl7X0tFFFprFDISAFUeUhABEQBTGlkQMhIPUiuPgKnKjMJJGlfboqKDsfUlMiIEZRtcFgsRN2UbaxYyTGlUSFgcAEBSV3UBSEZJU1dYBXsCEg9QG1wRChQ3VxpaFwA%253D&EA_PTAG=17078.27.503',
            ],
            [
                'item_url' => 'https://item.jd.com/64502249626.html',
                'short_url' => 'https://u.jd.com/uRtHQ3T',
                'product_title' => '金磨坊 网红辣条零食大礼包 网红同款大辣片辣丝儿童食品 童年美味的小零食 【掌柜荐】网红大面筋70*10袋-加购下单发11包',
                'product_main_picture' => 'https://img.duomai.com/20210222100214_qohjlddjc4.jpg',
                'product_original_price' => '28.8',
                'product_coupon_after_price' => '18.8',
                'wx_appid' => 'wx91d27dbf599dff74',
                'wx_path' => 'pages/union/proxy/proxy?spreadUrl=https%3A%2F%2Funion-click.jd.com%2Fjdc%3Fe%3D16282%26p%3DAyIGZRNSFgQWBVUSXSUFFwNUGFgQChUEUisfSlpMWGVCHlBDUAxLBQBNXURQAURETlcNVQtHRU1HRltKQh5JXxxFD19XEgcWBlYYXh0FEQBCW1diAHJhImgOV3V7XTJsAmpVdXU2ZCxhRBtiDXwkdV91WTEZOGl1SF4WfgRqa3EGHEU%252BSmltZzZ7U3Z7cl8haCRWdXtjNW8SYlV1TzJkO2FyUFADcBBmS3FGImk8ZnVxeBF7BGZwcmQiXDgXVEJzJmgYY3t2YCJ4LFBia1EzbChuQGp%252BB30sTHVQcwpBCGJKchNXbmtiampPFWQGdmdpHVRiXBdHYm4oGx9DDh43Uh5cEwsbAlErWxIBGwRlGVMTCxcGURhrFQMiRjsdWhYEFA5lGmsVBRICXB5YHAMVBFweaxUKFTeBjvDNiYVVFFmCpafErfsrayUBIgdSGFIWMiIHVitYJVx8BgYYUhcCQQY7QQZRWVNYEUI1FQIaAlIaWBYFIgVUGlkX&EA_PTAG=17078.27.503',
            ],
            [
                'item_url' => 'https://item.jd.com/48986926527.html',
                'short_url' => 'https://u.jd.com/u3tNVY9',
                'product_title' => '【2瓶装】川娃子烧椒酱炭烧辣椒酱特辣 虎皮青椒下饭菜剁椒酱四川农家自制拌面酱蒜蓉辣酱',
                'product_main_picture' => 'https://img14.360buyimg.com/pop/jfs/t1/141838/5/1296/265247/5ef19817E67f361fb/dd8b70e2ff57d622.jpg',
                'product_original_price' => '37.6',
                'product_coupon_after_price' => '27.6',
                'wx_appid' => 'wx91d27dbf599dff74',
                'wx_path' => 'pages/union/proxy/proxy?spreadUrl=https%3A%2F%2Funion-click.jd.com%2Fjdc%3Fe%3D16282%26p%3DAyIGZRtYFQsbBF0TWxcyFw5dElwdARUDVh1rUV1KWQorAlBHU0VeBUVNR0ZbSkAOClBMW0seUh0LFQ9WHF8WBA1eEEcGJVxnWlQbRRJ6cUInZBxARUBPFkQfbFQeC2UcXhIEGw5QH2sVBREOVitZHQQbAFMYWCUCEzcUdV0SBBUPUitaJQIVB1ASXhcHEwZcHVslAhoAZc%252FOvtqZkAdaGcyyt9H%252FtWslMhE3VRxYHAEiN1UrWCVcfAYGG1kQAEIAO0EGUVgRUwoeNRUCGgJcHV0TACIFVBpZFw%253D%253D&EA_PTAG=17078.27.503',
            ]
        ];

        return $this->jsonResponse($itemUrls);
    }

    /**
     * 统一输出
     *
     * @param array $data
     * @param bool $success
     * @param string $message
     */
    private function jsonResponse(array $data, $success = true, $message = '')
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





    /**
     * 根据item_id获取详情（与list中的一样，已废弃）
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
     * url链接解析商品与计划（已废弃，只使用geiLink）
     * 已废弃
     *
     * @param $itemUrl
     * @return void
     */
    public function linkHandle($itemUrl)
    {
        if (!$itemUrl) {
            $itemUrl = $_POST['item_url'];
        }

        try {
            $api = 'cps-mesh.cpslink.links.put';

            $data = $this->client->Request($api, [
                'url' => $itemUrl
            ]);

            return $this->jsonResponse($data);
        } catch (\Exception $exception) {
            return $this->jsonResponse([], false, 'network error');
        }
    }
}
