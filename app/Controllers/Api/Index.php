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

    public function getList()
    {
        $platform = $this->request('platform');
        $query = $this->request('query');
        $page = (int) $this->request('page') ?: 1;

        if (!in_array($platform, ['jd', 'pdd'])) {
            return $this->jsonResponse([], false, 'no support this platform');
        }

        // jd 使用京粉接口，不再使用通用接口，通用接口价格有问题，没有优惠卷领取
        if ($platform == 'jd') {
            return $this->getJingFenList();
        }
        // pdd 使用推荐-实时热销接口
        if ($platform == 'pdd') {
            return $this->getPddRecommendList();
        }

        try {
            $api = "cps-mesh.cpslink.{$platform}.products.get";

            $data = $this->client->Request($api, [
                'query' => $query,
                'page' => $page,
                'is_hot' => 1,
                'is_coupon' => 1,
                'max_coupon' => 1,
                'order_field' => 'volume ',  // 排序字段 commission_rate 佣金比例 price价格 volume 销量
            ]);

            return $this->jsonResponse($data);

        } catch (\Exception $exception) {
            return $this->jsonResponse([], false, 'network error');
        }
    }

    /**
     * 获取京粉商品列表
     */
    public function getJingFenList()
    {
        $categoryId = $this->request('category_id', 28);
        $page = (int) $this->request('page') ?: 1;

        try {
            $api = 'cps-mesh.cpslink.jd.jingfen-product.get';

            $data = $this->client->Request($api, [
                'category_id' => $categoryId,
                'page' => $page,
                'order_field' => 'commission_rate  ',  // 排序字段 commission_rate 佣金比例 price价格 volume 销量
            ]);

            return $this->jsonResponse($data);

        } catch (\Exception $exception) {
            return $this->jsonResponse([], false, 'network error');
        }
    }

    /**
     * 获取拼多多实时热销榜
     */
    public function getPddRecommendList()
    {
        $channelType = $this->request('channel_type', 1);    // 推荐类型 0-1.9包邮, 1-今日爆款, 2-品牌清仓,3-相似商品推荐,4-猜你喜欢,5-实时热销,6-实时收益,7-今日畅销,8-高佣榜单
        $page = (int) $this->request('page') ?: 1;

        try {
            $api = 'cps-mesh.cpslink.pdd.recommend-products.get';

            $data = $this->client->Request($api, [
                'channel_type' => $channelType,
                'page' => $page,
            ]);

            return $this->jsonResponse($data);

        } catch (\Exception $exception) {
            return $this->jsonResponse([], false, 'network error');
        }
    }

    /**
     * 获取拼多多实时热销榜（接口不稳定不推荐使用）
     */
    public function getPddTopList()
    {
        $channelType = $this->request('channel_type', 1);    // 类型 1-实时热销榜；2-实时收益榜
        $page = (int) $this->request('page') ?: 1;

        try {
            $api = 'cps-mesh.cpslink.pdd.top-products.get';

            $data = $this->client->Request($api, [
                'channel_type' => $channelType,
                'page' => $page,
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
        $query = $this->request('item_url');

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
        $itemUrls = ['data' => [
            [
                'item_url' => 'https://item.jd.com/100007827998.html',
                'short_url' => 'https://u.jd.com/uoPViuu',
                'seller_name' => '京东超市',
                'product_title' => '李子柒 广西柳州特产(煮食)袋装 方便速食面粉米线 螺蛳粉 335g*3包',
                'product_main_picture' => 'https://img.duomai.com/20210310003005_uai9hpux9x.jpg',
                'product_original_price' => '39.7',
                'product_coupon_after_price' => '39.7',
                'wx_appid' => 'wx91d27dbf599dff74',
                'wx_path' => 'pages/union/proxy/proxy?spreadUrl=https%3A%2F%2Funion-click.jd.com%2Fjdc%3Fe%3D16282%26p%3DAyIGZRhcFAQbBlQeXBwyEgZUGloTCxEBXRNSJUZNXwtEa0xHV0YXEEULWldTCQQAQB1AWQkFWxQDEwZTElgTChoOSkIeSV8iDwwSX0N2cEE2fDlDABUGNR0JfWprUVkXaxIHFQFcEl4RMhIAVhJYJQcSAlITXBIBIgdUKxl7WUdpUhNfFAZCAAZPXxAEGzdUK1sSAhYFUBNbEQQXBVUrWx0FItPAsIOelUBGF8LrsNS4qWUraxYyEgBWElglMhI3VisFewNBBAZPDhJRfF0JGBISWlReOxlYEwEUDlMrWRQDEAU%253D&EA_PTAG=17078.27.503',
            ],
            [
                'item_url' => 'https://item.jd.com/65443604411.html',
                'short_url' => 'https://u.jd.com/uJu6TNd',
                'seller_name' => '京东超市',
                'product_title' => 'RIO锐澳樱花风味微醺系列鸡尾酒330ml*10罐（七种口味）',
                'product_main_picture' => 'https://img14.360buyimg.com/pop/jfs/t1/156249/13/11903/95705/603c60c1E7d736c31/a3629165ecea4a0b.jpg',
                'product_original_price' => '88',
                'product_coupon_after_price' => '55.4',
                'wx_appid' => 'wx91d27dbf599dff74',
                'wx_path' => 'pages/union/proxy/proxy?spreadUrl=https%3A%2F%2Funion-click.jd.com%2Fjdc%3Fe%3D16282%26p%3DAyIGZRhcFgURBFwaWhcyFQNQHlkSAxcCVRtrUV1KWQorAlBHU0VeBUVNR0ZbSkAOClBMW0scXxAHEABUHl4VAg1eEEcGJVtqABcbPVdickUjeDNieW1%252BMkUNbnIeC2UcXhIEGw5QH2sVBREOViteFAEUAFUTWCUCEzcUdV0WBBcGZRprFQUSAl0TWRwLGwFRHGsVChU3gY7wzYmFVRRZgqWnxK37K2slASIHUhhSFjIiB2UYa0tsE1RWSAwRCxRpD0YSFV1VQ1J1WRYGFANdGGsXAxMFVw%253D%253D&EA_PTAG=17078.27.503',
            ],
            [
                'item_url' => 'https://item.jd.com/7532054.html',
                'short_url' => 'https://u.jd.com/urPJ210',
                'seller_name' => '京东超市',
                'product_title' => '卜珂零点 雪花酥蔓越莓味 110g 牛轧糖奶芙沙琪玛手工网红甜品办公室零食小吃糕点早餐',
                'product_main_picture' => 'https://img.duomai.com/20210305003005_jdqls1ga4u.jpg',
                'product_original_price' => '11.8',
                'product_coupon_after_price' => '11.8',
                'wx_appid' => 'wx91d27dbf599dff74',
                'wx_path' => 'pages/union/proxy/proxy?spreadUrl=https%3A%2F%2Funion-click.jd.com%2Fjdc%3Fe%3D16282%26p%3DAyIGZRprEwYQBFQfXiVGTV8LRGtMR1dGFxBFC1pXUwkEAEAdQFkJBV0RABEGUR5ETEdOWmVtXxF0ch1QGTscZERdARgdY1xgDiNdVxkyFQJSHVIcBxY3VRxYHAEiAlUeXRQHEARlG1olVHwHVBpaFwAQBlQZaxQyEgBVH1kQChcBVRtYETISD1Irj4CpyozCSRpX26Kig7H1JTIiBGUbXBYLETdlG2sWMkxpVEsLQgoQUFF1AUkBW1JcU1x7ABcEURNYHTIQBlQZWQ%253D%253D&EA_PTAG=17078.27.503',
            ],
            [
                'item_url' => 'https://item.jd.com/71043158714.html',
                'short_url' => 'https://u.jd.com/u0P5f8G',
                'seller_name' => '京东超市',
                'product_title' => '百事可乐 碳酸汽水饮料330*24听 细长罐 Pepsi百事出品 新老包装随机发',
                'product_main_picture' => 'https://img14.360buyimg.com/pop/jfs/t1/170932/15/193/124151/5fed8b8fEf030f771/6004c08ef8232078.jpg',
                'product_original_price' => '50.9',
                'product_coupon_after_price' => '42.9',
                'wx_appid' => 'wx91d27dbf599dff74',
                'wx_path' => 'pages/union/proxy/proxy?spreadUrl=https%3A%2F%2Funion-click.jd.com%2Fjdc%3Fe%3D16282%26p%3DAyIGZRhcFwMSDlQaWhAyFAdUHlkVBhsBVR5rUV1KWQorAlBHU0VeBUVNR0ZbSkAOClBMW0sdWxQHEAdREl0VBw1eEEcGJQcadTNrAhxBd3wBHTJOankCCB0MUVQeC2UcXhIEGw5QH2sVBREOViteFQcUBFQdWCUCEzcUdVsUBxMHVh5bJQMiB1IbXxcHGgFRG1ITASIHXRxrwZe5396MCVRAy7fwzfG7MiI3VitbEgEbBGUrWyUBIlk7GggVVxRQUR01T14RTg4TAVBsEAJQGFoSCyIFVBpZFw%253D%253D&EA_PTAG=17078.27.503',
            ],
            [
                'item_url' => 'https://item.jd.com/100013092738.html',
                'short_url' => 'https://u.jd.com/uuPXDQw',
                'seller_name' => '京东超市',
                'product_title' => '西麦 西澳阳光 水果燕麦片 营养代餐 麦片早餐  酸奶伴侣 干吃零食 轻食非油炸 酸奶果粒烘焙燕麦片350g',
                'product_main_picture' => 'https://img14.360buyimg.com/pop/jfs/t1/128668/4/158/114678/5eb3afeeE9fbd70dd/1812bec81f667323.jpg',
                'product_original_price' => '45.9',
                'product_coupon_after_price' => '45.9',
                'wx_appid' => 'wx91d27dbf599dff74',
                'wx_path' => 'pages/union/proxy/proxy?spreadUrl=https%3A%2F%2Funion-click.jd.com%2Fjdc%3Fe%3D16282%26p%3DAyIGZRhcFgQUA1MeXhQyEgZUGlsXAxoEUxlSJUZNXwtEa0xHV0YXEEULWldTCQQAQB1AWQkFWxQDEwdXGlMWBBAOSkIeSV8idy9gL3Jnd0c2Yz1MemtTL2YwYF5kd1kXaxIHFQFcEl4RMhIAVhJYJQcSAlMeWhcBIgdUKwh71aOXgJfZzJac396MXBIFIgZlG1wVBhADVBpcFwcbBGUbUxIyxpL%252Bw9CCUFNFjKv%252Bw6i8N2UrWCUCFQRcGGslAiIEZUU1FFERVAFOXEVsSFtWUhpdA0hpVxlYEgUSD2UZWhQAEA%253D%253D&EA_PTAG=17078.27.503',
            ],
            [
                'item_url' => 'https://item.jd.com/100010088575.html',
                'short_url' => 'https://u.jd.com/uj0CqMg',
                'seller_name' => '京东超市',
                'product_title' => '希腊进口哇尔塔Epsa混合果汁汽水果汁型碳酸饮料232ml*6瓶 玻璃瓶装饮品',
                'product_main_picture' => 'https://img14.360buyimg.com/pop/jfs/t1/167166/25/3508/253690/600e86f6Eb73ff38c/b97e52ded2badcab.jpg',
                'product_original_price' => '89',
                'product_coupon_after_price' => '89',
                'wx_appid' => 'wx91d27dbf599dff74',
                'wx_path' => 'pages/union/proxy/proxy?spreadUrl=https%3A%2F%2Funion-click.jd.com%2Fjdc%3Fe%3D16282%26p%3DAyIGZRtSFwQbBVYTXxMyEgZUGlsUAxsOUR1fJUZNXwtEa0xHV0YXEEULWldTCQQAQB1AWQkFWxQDEwdUGlIcBhQDSkIeSV8iAF1nPWIKRHg1axNhQ2YZXUM4Z0QTUVkXaxIHFQFcEl4RMhIAVhJYJQcSAlMfUxEBIgdUKxl715mig4zlwomzN1QrWxICFgVRGlgcChoFVStbHQUi08Cwg56VQEYXwuuw1LipZStrFjISAFYSWCUyEjdWKwV7A0EEXBkJQAd8XQkYElFAUkI7GVkQAhsFUStZFAMQBQ%253D%253D&EA_PTAG=17078.27.503',
            ],
            [
                'item_url' => 'https://item.jd.com/64502249626.html',
                'short_url' => 'https://u.jd.com/uRtHQ3T',
                'seller_name' => '京东自营',
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
                'seller_name' => '京东自营',
                'product_title' => '【2瓶装】川娃子烧椒酱炭烧辣椒酱特辣 虎皮青椒下饭菜剁椒酱四川农家自制拌面酱蒜蓉辣酱',
                'product_main_picture' => 'https://img14.360buyimg.com/pop/jfs/t1/141838/5/1296/265247/5ef19817E67f361fb/dd8b70e2ff57d622.jpg',
                'product_original_price' => '37.6',
                'product_coupon_after_price' => '27.6',
                'wx_appid' => 'wx91d27dbf599dff74',
                'wx_path' => 'pages/union/proxy/proxy?spreadUrl=https%3A%2F%2Funion-click.jd.com%2Fjdc%3Fe%3D16282%26p%3DAyIGZRtYFQsbBF0TWxcyFw5dElwdARUDVh1rUV1KWQorAlBHU0VeBUVNR0ZbSkAOClBMW0seUh0LFQ9WHF8WBA1eEEcGJVxnWlQbRRJ6cUInZBxARUBPFkQfbFQeC2UcXhIEGw5QH2sVBREOVitZHQQbAFMYWCUCEzcUdV0SBBUPUitaJQIVB1ASXhcHEwZcHVslAhoAZc%252FOvtqZkAdaGcyyt9H%252FtWslMhE3VRxYHAEiN1UrWCVcfAYGG1kQAEIAO0EGUVgRUwoeNRUCGgJcHV0TACIFVBpZFw%253D%253D&EA_PTAG=17078.27.503',
            ]
        ]];

        return $this->jsonResponse($itemUrls);
    }




    /**
     * 根据item_id获取详情（与list中的一样，已废弃）
     */
    public function getDetail()
    {
        $platform = $this->request('platform');
        $query = $this->request('item_id');

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
            $itemUrl = $this->request('item_url');
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
