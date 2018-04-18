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

namespace Sheep\payment\Exceptions;

/**
 * 支付网关异常类
 * Class GatewayException
 * @package Pay\Exceptions
 */
class GatewayException extends Exception
{
    /**
     * error raw data.
     * @var array
     */
    public $raw = [];

    /**
     * GatewayException constructor.
     * @param string $message
     * @param int $code
     * @param array $raw
     */
    public function __construct($message, $code, $raw = [])
    {
        parent::__construct($message, intval($code));
        $this->raw = $raw;
    }
}
