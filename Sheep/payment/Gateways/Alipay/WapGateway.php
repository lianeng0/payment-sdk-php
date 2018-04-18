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

namespace Sheep\payment\Gateways\Alipay;

use Sheep\payment\Gateways\Alipay;

/**
 * 手机WAP支付网关
 * Class WapGateway
 * @package Pay\Gateways\Alipay
 */
class WapGateway extends Alipay
{

    /**
     * 当前接口方法
     * @return string
     */
    protected function getMethod()
    {
        return 'alipay.trade.wap.pay';
    }

    /**
     * 当前接口产品码
     * @return string
     */
    protected function getProductCode()
    {
        return 'QUICK_WAP_WAY';
    }

    /**
     * 应用并返回参数
     * @param array $options
     * @return string
     */
    public function pay(array $options = [])
    {
        parent::pay($options);
        return $this->buildPayHtml();
    }
}
