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

namespace Sheep\payment\Gateways;

use InvalidArgumentException;
use Sheep\payment\Contracts\Config;
use Sheep\payment\Contracts\GatewayInterface;
use Sheep\payment\Contracts\HttpService;
use Sheep\payment\Exceptions\GatewayException;

/**
 * 支付宝抽象类
 * Class Alipay
 * @package Pay\Gateways\Alipay
 */
abstract class Alipay extends GatewayInterface
{

    /**
     * 支付宝全局参数
     * @var array
     */
    protected $config;

    /**
     * @var array
     */
    protected $payload;

    /**
     * 支付宝网关地址
     * @var string
     */
    protected $baseUri = 'https://openapi.alipay.com/gateway.do?charset=utf-8';

    /**
     * Alipay constructor.
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->config = new Config($config);
        if (is_null($this->config->get('app_id'))) {
            throw new InvalidArgumentException('Missing Config -- [app_id]');
        }
        if (!empty($config['cache_path'])) {
            HttpService::$cachePath = $config['cache_path'];
        }
        // 沙箱模式
        if (!empty($config['debug'])) {
            $this->gateway = 'https://openapi.alipaydev.com/gateway.do?charset=utf-8';
        }
        $this->payload = [
            'app_id'      => $this->config->get('app_id'),
            'method'      => '',
            'format'      => 'JSON',
            'charset'     => 'utf-8',
            'sign_type'   => 'RSA2',
            'version'     => '1.0',
            'return_url'  => $this->config->get('return_url', ''),
            'notify_url'  => $this->config->get('notify_url', ''),
            'timestamp'   => date('Y-m-d H:i:s'),
            'sign'        => '',
            'biz_content' => '',
        ];
    }

    /**
     * 应用参数
     * @param array $options
     * @return mixed|void
     */
    public function pay(array $options)
    {
        $options['product_code'] = $this->getProductCode();
        $this->payload['biz_content'] = json_encode($options);
        $this->payload['method'] = $this->getMethod();
        $this->payload['sign'] = $this->getSign();
    }

    /**
     * 支付宝订单退款操作
     * @param array|string $options 退款参数或退款商户订单号
     * @param null $refund_amount 退款金额
     * @return array|bool
     * @throws GatewayException
     */
    public function refund($options, $refund_amount = null)
    {
        if (!is_array($options)) {
            $options = ['out_trade_no' => $options, 'refund_amount' => $refund_amount];
        }
        return $this->getResult($options, 'alipay.trade.refund');
    }

    /**
     * 关闭支付宝进行中的订单
     * @param array|string $options
     * @return array|bool
     * @throws GatewayException
     */
    public function close($options)
    {
        if (!is_array($options)) {
            $options = ['out_trade_no' => $options];
        }
        return $this->getResult($options, 'alipay.trade.close');
    }

    /**
     * 查询支付宝订单状态
     * @param string $out_trade_no
     * @return array|bool
     * @throws GatewayException
     */
    public function find($out_trade_no = '')
    {
        $options = ['out_trade_no' => $out_trade_no];
        return $this->getResult($options, 'alipay.trade.query');
    }

    /**
     * 验证支付宝支付宝通知
     * @param array $data 通知数据
     * @param null $sign 数据签名
     * @param bool $sync
     * @return array|bool
     */
    public function verify($data, $sign = null, $sync = false)
    {
        if (is_null($this->config->get('public_key'))) {
            throw new InvalidArgumentException('Missing Config -- [public_key]');
        }
        $sign = is_null($sign) ? $data['sign'] : $sign;
        $res = "-----BEGIN PUBLIC KEY-----\n" . wordwrap($this->config->get('public_key'), 64, "\n", true) . "\n-----END PUBLIC KEY-----";
        $toVerify = $sync ? json_encode($data) : $this->getSignContent($data, true);
        return openssl_verify($toVerify, base64_decode($sign), $res, OPENSSL_ALGO_SHA256) === 1 ? $data : false;
    }

    /**
     * @return string
     */
    protected function buildPayHtml()
    {
        $html = "<form id='alipaysubmit' name='alipaysubmit' action='{$this->baseUri}' method='post'>";
        foreach ($this->payload as $key => $value) {
            $value = str_replace("'", '&apos;', $value);
            $html .= "<input type='hidden' name='{$key}' value='{$value}'/>";
        }
        $html .= "<input type='submit' value='ok' style='display:none;'></form>";
        return $html . "<script>document.forms['alipaysubmit'].submit();</script>";
    }

    /**
     * 获取验证访问数据
     * @param array $options
     * @param string $method
     * @return array|bool
     * @throws GatewayException
     */
    protected function getResult($options, $method)
    {
        $this->payload['method'] = $method;
        $this->payload['biz_content'] = json_encode($options);
        $this->payload['sign'] = $this->getSign();
        $method = str_replace('.', '_', $method) . '_response';
        $data = json_decode($this->post($this->baseUri, $this->payload), true);
        if (!isset($data[$method]['code']) || $data[$method]['code'] !== '10000') {
            throw new GatewayException(
                "\nResultError" .
                (empty($data[$method]['code']) ? '' : "\n{$data[$method]['msg']}[{$data[$method]['code']}]") .
                (empty($data[$method]['sub_code']) ? '' : "\n{$data[$method]['sub_msg']}[{$data[$method]['sub_code']}]\n"),
                $data[$method]['code'],
                $data
            );
        }
        return $this->verify($data[$method], $data['sign'], true);
    }

    /**
     * 获取数据签名
     * @return string
     */
    protected function getSign()
    {
        if (is_null($this->config->get('private_key'))) {
            throw new InvalidArgumentException('Missing Config -- [private_key]');
        }
        $res = "-----BEGIN RSA PRIVATE KEY-----\n" .
            wordwrap($this->config->get('private_key'), 64, "\n", true) .
            "\n-----END RSA PRIVATE KEY-----";
        openssl_sign($this->getSignContent($this->payload), $sign, $res, OPENSSL_ALGO_SHA256);
        return base64_encode($sign);
    }

    /**
     * 数据签名处理
     * @param array $toBeSigned
     * @param bool $verify
     * @return bool|string
     */
    protected function getSignContent(array $toBeSigned, $verify = false)
    {
        ksort($toBeSigned);
        $stringToBeSigned = '';
        foreach ($toBeSigned as $k => $v) {
            if ($verify && $k != 'sign' && $k != 'sign_type') {
                $stringToBeSigned .= $k . '=' . $v . '&';
            }
            if (!$verify && $v !== '' && !is_null($v) && $k != 'sign' && '@' != substr($v, 0, 1)) {
                $stringToBeSigned .= $k . '=' . $v . '&';
            }
        }
        $stringToBeSigned = substr($stringToBeSigned, 0, -1);
        unset($k, $v);
        return $stringToBeSigned;
    }

    /**
     * @return string
     */
    abstract protected function getMethod();

    /**
     * @return string
     */
    abstract protected function getProductCode();
}
