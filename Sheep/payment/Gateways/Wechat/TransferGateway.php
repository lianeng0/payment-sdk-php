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

use Sheep\payment\Exceptions\GatewayException;

use Sheep\payment\Gateways\Wechat;

/**
 * 微信企业打款网关
 * Class TransferGateway
 * @package Pay\Gateways\Wechat
 */
class TransferGateway extends Wechat
{

    /**
     * 支付网关
     * @var string
     */
    protected $gateway = 'mmpaymkttransfers/promotion/transfers';

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
        return '';
    }

    /**
     * 应用并返回数据
     * @param array $options
     * @return array
     * @throws GatewayException
     */
    public function pay(array $options = [])
    {
        $options['mchid'] = $this->payload['mch_id'];
        $options['mch_appid'] = $this->config->get('app_id');
        unset($this->payload['appid']);
        unset($this->payload['mch_id']);
        unset($this->payload['sign_type']);
        unset($this->payload['trade_type']);
        unset($this->payload['notify_url']);
        return $this->preOrder($options);
    }
}
