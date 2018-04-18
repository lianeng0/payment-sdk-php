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

namespace Sheep\payment\Gateways\Wechat;

use Sheep\payment\Gateways\Wechat;

/**
 * 微信POS刷卡支付网关
 * Class PosGateway
 * @package Sheep\payment\Gateways\Wechat
 */
class PosGateway extends Wechat
{

    /**
     * 支付网关
     * @var string
     */
    protected $gateway = 'pay/micropay';

    /**
     * 当前支付网关
     * @return string
     */
    protected function getTradeGateway()
    {
        return $this->gateway;
    }

    /**
     * 当前操作类型
     * @return string
     */
    protected function getTradeType()
    {
        return 'MICROPAY';
    }

    /**
     * 应用并返回参数
     * @param array $options
     * @return array
     * @throws \Sheep\payment\Exceptions\GatewayException
     */
    public function pay(array $options = [])
    {
        unset($this->config['trade_type']);
        unset($this->config['notify_url']);
        return $this->preOrder($options);
    }

}
