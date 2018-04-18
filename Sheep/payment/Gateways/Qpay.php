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

use Sheep\payment\Contracts\Config;
use Sheep\payment\Contracts\GatewayInterface;
use Sheep\payment\Contracts\HttpService;
use Sheep\payment\Exceptions\GatewayException;
use Sheep\payment\Exceptions\InvalidArgumentException;

/**
 * Class Qpay
 * @package Sheep\payment\Gateways
 */
abstract class Qpay extends GatewayInterface
{
    /**
     * Config.
     *
     * @var Config
     */
    protected $config;

    /**
     * Qpay payload.
     *
     * @var array
     */
    protected $payload;

    /**
     * Qpay gateway.
     *
     * @var string
     */
    protected $baseUrl = 'https://qpay.qq.com/cgi-bin/';

    /**
     * Qpay constructor.
     * @param $config
     */
    public function __construct($config)
    {
        $this->config = new Config($config);
        //商户ID
        if (is_null($this->config->get('mch_id'))) {
            throw new InvalidArgumentException('Missing Config -- [mch_id]');
        }
        //商户KEY
        if (is_null($this->config->get('mch_key'))) {
            throw new InvalidArgumentException('Missing Config -- [mch_key]');
        }
        //缓存位置
        if (!empty($config['cache_path'])) {
            HttpService::$cachePath = $config['cache_path'];
        }
        $this->payload = [
            'mch_id'           => $this->config->get('mch_id', ''),
            'nonce_str'        =>  $this->createNonceStr(),
            'notify_url'       => $this->config->get('notify_url', ''),
            'fee_type'         => 'CNY',
            'sign'             => '',
            'trade_type'       => '',
        ];
    }

    /**
     * 订单退款操作
     * @param array $options
     * @return array
     * @throws GatewayException
     */
    public function refund($options = [])
    {
        $this->payload = array_merge($this->payload, $options);
        $this->payload['op_user_id'] = isset($this->payload['op_user_id']) ?: $this->config['mch_id'];
        $this->unsetTradeTypeAndNotifyUrl();
        return $this->getResult('secapi/pay/refund', true);
    }

    /**
     * 关闭正在进行的订单
     * @param string $out_trade_no
     * @return array
     * @throws GatewayException
     */
    public function close($out_trade_no = '')
    {
        $this->config['out_trade_no'] = $out_trade_no;
        $this->unsetTradeTypeAndNotifyUrl();
        return $this->getResult('pay/closeorder');
    }

    /**
     * 查询订单状态
     * @param string $out_trade_no
     * @return array
     * @throws GatewayException
     */
    public function find($out_trade_no = '')
    {
        $this->config['out_trade_no'] = $out_trade_no;
        $this->unsetTradeTypeAndNotifyUrl();
        return $this->getResult('pay/orderquery');
    }

    /**
     * XML内容验证
     * @param string $data
     * @param null $sign
     * @param bool $sync
     * @return array|bool
     */
    public function verify($data, $sign = null, $sync = false)
    {
        $data = $this->fromXml($data);
        $sign = is_null($sign) ? $data['sign'] : $sign;
        return $this->getSign($data) === $sign ? $data : false;
    }

    /**
     * @param array $options
     * @return array
     * @throws GatewayException
     */
    protected function preOrder($options = [])
    {
        //构建支付数据
        $this->payload = array_merge($this->payload, $options);
        //生成数据签名
        $this->payload['sign'] = $this->getSign($this->payload);
        return $this->getResult($this->getTradeGateway());
    }

    /**
     * 获取验证访问数据
     * @param string $url
     * @param bool $cert
     * @return array
     * @throws GatewayException
     */
    protected function getResult($url, $cert = false)
    {
        $result = $this->fromXml(
            $this->post($this->baseUrl . $url, $this->toXml($this->payload),
                $cert ? ['ssl_cer' => $this->config->get('ssl_cer', ''), 'ssl_key' => $this->config->get('ssl_key', '')]: '')
        );
        halt($result);
        if (!isset($result['return_code']) || $result['return_code'] !== 'SUCCESS' || $result['result_code'] !== 'SUCCESS') {
            $error = 'ResultError:' . $result['return_msg'];
            $error .= isset($result['err_code_des']) ? ' - ' . $result['err_code_des'] : '';
        }
        if (isset($result['sign'])) {
            if (!isset($error) && $this->getSign($result) !== $result['sign']) {
                $error = 'GetResultError: return data sign error';
            }
        }
        if (isset($error)) {
            throw new GatewayException($error, 10000, $result);
        }
        return $result;
    }


    /**
     * 生成内容签名
     * @param $data
     * @return string
     */
    protected function getSign($data)
    {
        if (is_null($this->config->get('mch_key'))) {
            throw new InvalidArgumentException('Missing Config -- [mch_key]');
        }
        ksort($data);
        $string = md5($this->getSignContent($data) . '&key=' . $this->config->get('mch_key'));
        return strtoupper($string);
    }

    /**
     * 生成签名内容
     * @param $data
     * @return string
     */
    private function getSignContent($data)
    {
        $buff = '';
        foreach ($data as $k => $v) {
            $buff .= ($k != 'sign' && $v != '' && !is_array($v)) ? $k . '=' . $v . '&' : '';
        }
        return trim($buff, '&');
    }

    /**
     * 生成随机字符串
     * @param int $length
     * @return string
     */
    protected function createNonceStr($length = 16)
    {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $str = '';
        for ($i = 0; $i < $length; $i++) {
            $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
        }
        return $str;
    }

    /**
     * 转为XML数据
     * @param array $data 源数据
     * @return string
     */
    protected function toXml($data)
    {
        if (!is_array($data) || count($data) <= 0) {
            throw new InvalidArgumentException('convert to xml error !invalid array!');
        }
        $xml = '<xml>';
        foreach ($data as $key => $val) {
            $xml .= (is_numeric($val) ? "<{$key}>{$val}</{$key}>" : "<{$key}><![CDATA[{$val}]]></{$key}>");
        }
        return $xml . '</xml>';
    }

    /**
     * 解析XML数据
     * @param string $xml 源数据
     * @return mixed
     */
    protected function fromXml($xml)
    {
        if (!$xml) {
            throw new InvalidArgumentException('convert to array error !invalid xml');
        }
        libxml_disable_entity_loader(true);
        return json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA), JSON_UNESCAPED_UNICODE), true);
    }

    /**
     * 清理签名验证不必要的参数
     * @return bool
     */
    protected function unsetTradeTypeAndNotifyUrl()
    {
        unset($this->payload['notify_url']);
        unset($this->payload['trade_type']);
        return true;
    }

    /**
     * 获取当前支付方式
     * @return mixed
     */
    abstract protected function getTradeType();

    /**
     * 当前支付网关
     * @return mixed
     */
    abstract protected function getTradeGateway();
}