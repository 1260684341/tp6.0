<?php
namespace app\common\easyAlipay;

require root_path() . '/vendor/autoload.php';
use Alipay\EasySDK\Kernel\Factory;
use Alipay\EasySDK\Kernel\Config;

class EasyAlipayFactory
{
    static private $instance;

    //防止使用new直接创建对象
    private function __construct() {}

    //防止使用clone克隆对象
    private function __clone() {}

    public static $config = [
        // 测试运行配置
        'test' => [
            'protocol' => 'https',
            'gatewayHost' => 'openapi.alipaydev.com',
            'signType' => 'RSA2',
            'appId' => '', //  请填写您的AppId，例如：2019022663440152
            'privateKeyPath' => __DIR__ . '/cert/test/merchantPrivateKey.txt', // 请填写您的应用私钥，例如：MIIEvQIBADANB ，为避免私钥随源码泄露，推荐从文件中读取私钥字符串而不是写入源码中，
            'alipayCertPath' => __DIR__ . '/cert/test/alipayCertPublicKey_RSA2.crt', // 请填写您的支付宝公钥证书文件路径，例如：/foo/alipayCertPublicKey_RSA2.crt
            'alipayRootCertPath' => __DIR__ . '/cert/test/alipayRootCert.crt', // 请填写您的支付宝根证书文件路径，例如：/foo/alipayRootCert.crt
            'merchantCertPath' => __DIR__ . '/cert/test/appCertPublicKey_2016091100485081.crt', // 请填写您的应用公钥证书文件路径，例如：/foo/appCertPublicKey_2019051064521003.crt
            'alipayPublicKey' => '', // 如果采用非证书模式，则无需赋值上面的三个证书路径，改为赋值如下的支付宝公钥字符串即可，请填写您的支付宝公钥
            'notifyUrl' => '', // 可设置异步通知接收服务地址（可选）
            'encryptKey' => '', // 可设置AES密钥，调用AES加解密相关接口时需要（可选）
        ],

        // 正式运行配置
        'product' => [
            'protocol' => 'https',
            'gatewayHost' => 'openapi.alipay.com',
            'signType' => 'RSA2',
            'appId' => '', //  请填写您的AppId，例如：2019022663440152
            'privateKeyPath' => __DIR__ . '/cert/product/merchantPrivateKey.txt', // 请填写您的应用私钥，例如：MIIEvQIBADANB ，为避免私钥随源码泄露，推荐从文件中读取私钥字符串而不是写入源码中，
            'alipayCertPath' => __DIR__ . '/cert/product/alipayCertPublicKey_RSA2.crt', // 请填写您的支付宝公钥证书文件路径，例如：/foo/alipayCertPublicKey_RSA2.crt
            'alipayRootCertPath' => __DIR__ . '/cert/product/alipayRootCert.crt', // 请填写您的支付宝根证书文件路径，例如：/foo/alipayRootCert.crt
            'merchantCertPath' => __DIR__ . '/cert/product/appCertPublicKey_2021001181659376.crt', // 请填写您的应用公钥证书文件路径，例如：/foo/appCertPublicKey_2019051064521003.crt
            'alipayPublicKey' => '', // 如果采用非证书模式，则无需赋值上面的三个证书路径，改为赋值如下的支付宝公钥字符串即可，请填写您的支付宝公钥
            'notifyUrl' => '', // 可设置异步通知接收服务地址（可选）
            'encryptKey' => '', // 可设置AES密钥，调用AES加解密相关接口时需要（可选）
        ],
    ];


    public static function instance($config = [])
    {
        if (!self::$instance instanceof self || !empty($config)) {
            self::$instance = Factory::setOptions(self::init($config));
        }
        return self::$instance;
    }

    private static function init($config = [])
    {
        $options = new Config();
        $app_debug = config('app_debug');
        if ($app_debug) {
            $config = array_merge(self::$config['test'], $config);
        }
        else {
            $config = array_merge(self::$config['product'], $config);
        }

        $options->protocol = $config['protocol'] ?? 'https';
        $options->gatewayHost = $config['gatewayHost'] ?? 'openapi.alipay.com';
        $options->signType = $config['signType'] ?? 'RSA2';

        $options->appId = $config['appId'] ?? '';

        // 请填写您的应用私钥;
        $options->merchantPrivateKey = is_file($config['privateKeyPath']) ? file_get_contents($config['privateKeyPath']) : $config['privateKeyPath'];

        // 请填写您的支付宝公钥证书文件路径，例如：/foo/alipayCertPublicKey_RSA2.crt
        $options->alipayCertPath = $config['alipayCertPath'];

        // 请填写您的支付宝根证书文件路径，例如：/foo/alipayRootCert.crt
        $options->alipayRootCertPath = $config['alipayRootCertPath'];

        // 请填写您的应用公钥证书文件路径，例如：/foo/appCertPublicKey_2019051064521003.crt
        $options->merchantCertPath = $config['merchantCertPath'];

        //注：如果采用非证书模式，则无需赋值上面的三个证书路径，改为赋值如下的支付宝公钥字符串即可
        // $options->alipayPublicKey = $config['alipayPublicKey']; // 请填写您的支付宝公钥，例如：MIIBIjANBg;

        //可设置异步通知接收服务地址（可选）
        $options->notifyUrl = $config['notifyUrl'];

        //可设置AES密钥，调用AES加解密相关接口时需要（可选）
        $options->encryptKey = $config['encryptKey'];
        return $options;
    }

}