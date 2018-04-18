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

namespace Sheep\payment\Contracts;

use ArrayAccess;
use Sheep\payment\Exceptions\InvalidArgumentException;

/**
 * 支付配置对象
 * Class Config
 * @package Pay\Contracts
 */
class Config implements ArrayAccess
{
    /**
     * 配置参数
     * @var array
     */
    protected $config;

    /**
     * Config constructor.
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        $this->config = $config;
    }

    /**
     * 读取配置选项
     * @param null $key
     * @param null $default
     * @return array|mixed|null
     */
    public function get($key = null, $default = null)
    {
        if (is_null($key)) {
            return  $this->config;
        }
        if (isset( $this->config[$key])) {
            return  $this->config[$key];
        }
        foreach (explode('.', $key) as $segment) {
            if (!is_array( $this->config) || !array_key_exists($segment,  $this->config)) {
                return $default;
            }
            $config =  $this->config[$segment];
        }
        return  $config;
    }

    /**
     * 设置配置选项
     * @param string $key
     * @param string $value
     * @return array
     */
    public function set($key, $value)
    {
        if ($key == '') {
            throw new InvalidArgumentException('Invalid config key.');
        }
        // 只支持三维数组，多余无意义
        $keys = explode('.', $key);
        switch (count($keys)) {
            case '1':
                $this->config[$key] = $value;
                break;
            case '2':
                $this->config[$keys[0]][$keys[1]] = $value;
                break;
            case '3':
                $this->config[$keys[0]][$keys[1]][$keys[2]] = $value;
                break;
            default:
                throw new InvalidArgumentException('Invalid config key.');
        }
        return $this->config;
    }

    /**
     * 判断是否有配置
     * @param string $offset
     * @return bool
     */
    public function offsetExists($offset)
    {
        return array_key_exists($offset, $this->config);
    }

    /**
     * 获取配置对象
     * @param string $offset
     * @return array|mixed|null
     */
    public function offsetGet($offset)
    {
        return $this->get($offset);
    }

    /**
     * 设置配置对象
     * @param string $offset
     * @param array|mixed|null $value
     */
    public function offsetSet($offset, $value)
    {
        $this->set($offset, $value);
    }

    /**
     * 清除设置对象
     * @param string $offset
     */
    public function offsetUnset($offset)
    {
        $this->set($offset, null);
    }
}
