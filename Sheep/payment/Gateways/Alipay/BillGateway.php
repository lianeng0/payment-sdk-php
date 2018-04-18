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
 * 支付宝电子面单下载
 * Class BillGateway
 * @package Pay\Gateways\Alipay
 */
class BillGateway extends Alipay
{

    /**
     * 当前接口方法
     * @return string
     */
    protected function getMethod()
    {
        return 'alipay.data.dataservice.bill.downloadurl.query';
    }


    /**
     * 应用并返回参数
     * @return array|bool
     */
    protected function getProductCode()
    {
        return '';
    }

    /**
     * 应用并返回参数
     * @param array $options
     * @return array|bool
     * @throws \Sheep\payment\Exceptions\GatewayException
     */
    public function pay(array $options = [])
    {
        return $this->getResult($options, $this->getMethod());
    }
}