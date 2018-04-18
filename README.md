# payment-sdk-php
PHP支付SDK（QQ钱包支付 + 微信支付 + 支付宝支付）

- 欢迎`Star`，欢迎`Fork`！
- 项目以用于实践  案例  [Mofee聚合支付](https://www.98imo.com/)
- 项目设计及部分源码参考于 [yansongda/pay](https://github.com/yansongda/pay)，在此特别感谢！

## 特点
- 代码简洁，无需加载多余组件，可应用于任何平台或框架
- 隐藏开发者不需要关注的细节，完全内部实现
- 根据支付宝、微信和QQ支付最新`API`开发集成
- 高度抽象的类，免去各种拼`json`与`xml`的痛苦
- 符合`PSR`标准，你可以各种方便的与你的框架集成
- 文件结构清晰易理解，可以随心所欲添加本项目中没有的支付网关
- 方法使用更优雅，不必再去研究那些奇怪的的方法名或者类名是做啥用的

## 声明
- 代码与框架部分参考于互联网开源项目
- 此`SDK`全部源码基于`MIT`协议开源，完全免费

## 环境
- PHP 5.6+

## 配置
```php
$config = [
            'wechat' => [
                'app_id'    =>'',
                'mch_id'    =>'',
                'mch_key'   =>'',
                'ssl_cer'   =>'',
                'ssl_key'   =>'',
                'notify_url'=>''
            ],
            'alipay' => [
                //应用ID,您的APPID。
                'app_id' => "",
                //商户私钥, 请把生成的私钥文件中字符串拷贝在此
                'private_key'    =>'',
                //支付宝公钥,查看地址：https://openhome.alipay.com/platform/keyManage.htm 对应APPID下的支付宝公钥。
                'public_key' => '',
                //异步通知地址
                'notify_url' => "",
                //同步跳转
                'return_url' => "",
                //沙箱
                'debug'           => false,
            ],
            'qpay' => [
                // QQ钱包分配的商户号
                'mch_id'          => '',
                // Qpay商户号
                'sub_mch_id'      => '',
                // Qpay支付签名秘钥
                'mch_key'         => '',
                //异步通知地址
                'notify_url'      => '',
                'ssl_cer'         => '',
                'ssl_key'         => '',
                //沙箱
                'debug'           => false,
            ],
        ];
```

## 架构

由于各支付网关参差不齐，所以我们抽象了两个方法 `driver()`，`gateway()`。

两个方法的作用如下：

`driver()` ： 确定支付平台，如 `alipay`,`wechat`,`qpay`;  
`gateway()`： 确定支付网关，如 `app`,`pos`,`scan`,`transfer`,`wap`,`web`,`...`

具体实现可以查看源代码。

### 1、支付宝

SDK 中对应的 driver 和 gateway 如下表所示：  

| driver | gateway |   描述       |
| :----: | :-----: | :-------:   |
| alipay | web     | 电脑支付     |
| alipay | wap     | 手机网站支付  |
| alipay | app     | APP 支付  |
| alipay | pos     | 刷卡支付  |
| alipay | scan    | 扫码支付  |
| alipay | bill    | 电子账单  |
| alipay | transfer | 帐户转账  |

### 2、微信

SDK 中对应的 driver 和 gateway 如下表所示：

| driver | gateway |   描述     |
| :----: | :-----: | :-------: |
| wechat | mp      | 公众号支付  |
| wechat | miniapp | 小程序支付  |
| wechat | wap     | H5 支付  |
| wechat | scan    | 扫码支付   |
| wechat | pos     | 刷卡支付    |
| wechat | app     | APP 支付   |
| wechat | bill    | 电子账单   |
| wechat | transfer  | 企业付款到零钱  |

### 3、QQ

SDK 中对应的 driver 和 gateway 如下表所示：

| driver | gateway |   描述     |
| :----: | :-----: | :-------: |
| qpay | wap     | H5 支付  |
| qpay | scan    | 扫码支付   |

更多方式正在赶来...

## 操作

所有网关均支持以下方法

- pay(array $options)  
说明：支付发起接口  
参数：数组类型，订单业务配置项，包含 订单号，订单金额等  
返回：mixed

- refund(array|string $options, $refund_amount = null)  
说明：发起退款接口  
参数：`$options` 为字符串类型仅对`支付宝支付`有效，此时代表订单号，第二个参数为退款金额。  
返回：mixed  退款成功，返回 服务器返回的数组；否则返回 false；  

- close(array|string $options)  
说明：关闭订单接口  
参数：`$options` 为字符串类型时代表订单号，如果为数组，则为关闭订单业务配置项，配置项内容请参考各个支付网关官方文档。  
返回：mixed  关闭订单成功，返回 服务器返回的数组；否则返回 false；  

- find(string $out_trade_no)  
说明：查找订单接口  
参数：`$out_trade_no` 为订单号。  
返回：mixed  查找订单成功，返回 服务器返回的数组；否则返回 false；  

- verify($data, $sign = null)  
说明：验证服务器返回消息是否合法  
参数：`$data` 为服务器接收到的原始内容，`$sign` 为签名信息，当其为空时，系统将自动转化 `$data` 为数组，然后取 `$data['sign']`。  
返回：mixed  验证成功，返回 服务器返回的数组；否则返回 false；  

## 实例
```php
// 实例支付对象
$pay = new \Sheep\payment\Pay($config);

try {
    $options = $pay->driver('alipay')->gateway('wap')->apply($payOrder);
    var_dump($options);
} catch (Exception $e) {
    echo "创建订单失败，" . $e->getMessage();
}
```
## 聚合使用示例
```php
 if ($this->request->isPost()){
            $driver = $this->request->post('driver');
            $gateway = $this->request->post('gateway');
            switch ($driver){
                case 'alipay':
                    $order = [
                        'out_trade_no' => time(),
                        'total_amount' => '0.01', //微信 total_fee
                        'subject'      => 'test subject-测试订单',//微信 body
                    ];
                    break;
                default:
                    $order = [
                        'out_trade_no' => time(),
                        'total_fee' => '1', //微信 total_fee  //单位：分
                        'body'      => 'test subject-测试订单',//微信 body
                    ];
                    break;
            }
            $pay = new Pay($config);
            $result = $pay->driver($driver)->gateway($gateway)->pay($order);
            halt($result);
        }
    }
```
## 通知

#### 支付宝
```php
// 实例支付对象
$pay = new \Sheep\payment\Pay($config);

$verify = $pay->driver('alipay')->gateway()->verify($_POST);
if ($verify) {
  //TODO 支付成功操作
} else {
  //验签失败
}
```

#### 微信
```php
$pay = new \Pay\Pay($config);
$verify = $pay->driver('wechat')->gateway('mp')->verify(file_get_contents('php://input'));

if ($verify) {
  //TODO 支付成功操作
} else {
  //验签失败
}

echo "success";
```

## 安装
```shell
// 方法一、 使用git安装
git clone https://github.com/singlesheep/payment-sdk-php

```
