<?php
namespace app\common\interfaceObj;

interface ITrade {

    const PAY_TYPE_WECHAT_GZH = 10; // 微信公众号
    const PAY_TYPE_WECHAT_XCX = 11; // 微信小程序
    const PAY_TYPE_WECHAT_H5 = 12; // 普通H5 微信支付
    const PAY_TYPE_WECHAT_NATIVE = 13; // 微信NATIVE
    const PAY_TYPE_WECHAT_APP = 14; // 微信App支付
    const PAY_TYPE_ALIPAY_H5 = 20; // 支付宝H5
    const PAY_TYPE_ALIPAY_APP = 21; // 支付宝APP
    const PAY_TYPE_BALANCE = 90;//余额支付
    const PAY_TYPE_OFFLINE = 91;//线下打款

    // 支付平台
    const PAY_PLATFORM_WECHAT = 1; // 微信支付
    const PAY_PLATFORM_ALIPAY = 2; // 支付宝支付
    const PAY_PLATFORM_UNION = 3; // 银联支付
    const PAY_PLATFORM_BALANCE = 4; // 余额支付
    const PAY_PLATFORM_OFFLINE = 8;    // 线下
    const PAY_PLATFORM_OTHERS = 9; // 其他

    public function whenPaySuccess($out_trade_no, $pay_platform);
    public function whenPayError($errmsg);
    public function whenRefundSuccess($out_trade_no);
    public function whenRefundError($errmsg);
}