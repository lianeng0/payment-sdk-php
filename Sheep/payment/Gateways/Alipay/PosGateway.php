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
 * 支付宝刷卡支付
 * Class PosGateway
 * @package Pay\Gateways\Alipay
 */
class PosGateway extends Alipay
{

    /**
     * 当前接口方法
     * @return string
     */
    protected function getMethod()
    {
        return 'alipay.trade.pay';
    }

    /**
     * 当前接口产品码
     * @return string
     */
    protected function getProductCode()
    {
        return 'FACE_TO_FACE_PAYMENT';
    }

    /**
     * 应用并返回参数
     * @param array $options
     * @param string $scene
     * @return array|bool
     * @throws \Sheep\payment\Exceptions\GatewayException
     */
    public function pay(array $options = [], $scene = 'bar_code')
    {
        $options['scene'] = $scene;
        return $this->getResult($options, $this->getMethod());
    }
}
