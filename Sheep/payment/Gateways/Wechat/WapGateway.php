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
 * 微信WAP网页支付网关
 * Class WapGateway
 * @package Sheep\payment\Gateways\Wechat
 */
class WapGateway extends Wechat
{
    /**
     * 支付网关
     * @var string
     */
    protected $gateway = 'pay/unifiedorder';

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
        return 'MWEB';
    }

    /**
     * 应用并生成参数
     * @param array $options
     * @param string $return_url
     * @return string
     * @throws \Sheep\payment\Exceptions\GatewayException
     */
    public function pay(array $options = [], $return_url = '')
    {
        $data = $this->preOrder($options);
        $data['mweb_url'] = isset($data['mweb_url']) ? $data['mweb_url'] : '';
        if (empty($return_url)) {
            $return_url = $this->config->get('return_url');
        }
        return $data['mweb_url'] . "&redirect_url=" . urlencode($return_url);
    }
}
