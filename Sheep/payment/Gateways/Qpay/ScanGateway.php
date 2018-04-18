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
namespace Sheep\payment\Gateways\Qpay;

use Sheep\payment\Gateways\Qpay;

/**
 * 扫码支付
 * Class ScanGateway
 * @package Sheep\payment\Gateways\Qpay
 */
class ScanGateway extends Qpay
{

    /**
     * 支付网关
     * @var string
     */
    protected $gateway = 'pay/qpay_unified_order.cgi';

    /**
     * 当前支付网关
     * @return string
     */
    protected function getTradeGateway()
    {
        return $this->gateway;
    }

    /**
    * 应用并返回参数
    * @param array $options
    * @return mixed
    * @throws \Sheep\payment\Exceptions\GatewayException
    */
    public function pay(array $options = [])
    {
        return $this->preOrder($options);
    }

    /**
     * Get trade type config.
     * @return string
     */
    protected function getTradeType(): string
    {
        return 'NATIVE';
    }
}
