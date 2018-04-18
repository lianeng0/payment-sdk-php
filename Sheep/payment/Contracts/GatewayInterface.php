<?php
/**
 *  +----------------------------------------------------------------------
 *  | php聚合支付SDK
 *  +----------------------------------------------------------------------
 *  | 开源协议 ( https://mit-license.org )
 *  +----------------------------------------------------------------------
 *  | github开源项目：https://github.com/singlesheep/payment-sdk-php
 *  +----------------------------------------------------------------------
 *  | 项目设计及部分源码参考于 yansongda/pay，在此特别感谢！
 *  +----------------------------------------------------------------------
 */

namespace Sheep\payment\Contracts;

/**
 * 支付网关接口
 * Interface GatewayInterface
 * @package Sheep\payment\Contracts
 */
abstract class GatewayInterface
{
    /**
     * 发起支付
     * @param array $options
     * @return mixed
     */
    abstract public function pay(array $options);

    /**
     * 订单退款
     * @param $options
     * @return mixed
     */
    abstract public function refund($options);

    /**
     * 关闭订单
     * @param $options
     * @return mixed
     */
    abstract public function close($options);

    /**
     * 查询订单
     * @param $out_trade_no
     * @return mixed
     */
    abstract public function find($out_trade_no);

    /**
     * 通知验证
     * @param array $data
     * @param null $sign
     * @param bool $sync
     * @return mixed
     */
    abstract public function verify($data, $sign = null, $sync = false);

    /**
     * 网络模拟请求
     * @param string $url 网络请求URL
     * @param array|string $data 请求数据
     * @param array $options
     * @return bool|string
     */
    public function post($url, $data, $options = [])
    {
        return HttpService::post($url, $data, $options);
    }
}
