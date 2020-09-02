<?php

namespace app\notify\controller;

use app\common\easyAlipay\EasyAlipayFactory;
use app\common\kit\XmlKit;
use app\common\logic\WxRefund;
use app\common\logic\ZfbPay;
use app\common\model\WxPay;
use app\common\logic\WxPay as WxPayLogic;
use think\Request;
use wechat\EasyWechatFactory;

class Index
{
    // 微信支付回调
    public function wxPayNotify(Request $request)
    {
        $input = XmlKit::xmlDecode(file_get_contents("php://input"));
        file_put_contents('log.log', file_get_contents("php://input"));
        if (empty($input['appid'])) {
            return false;
        }
        $easyWechat = EasyWechatFactory::payment(array_merge(EasyWechatFactory::getConfig("pay"), [
            'appid' => $input['appid'],
        ]));
        $response = $easyWechat->handlePaidNotify(function ($message, $fail) {
            try {
                //查询微信订单是否真正交易成功
                $wx_pay = new WxPay();
                $row_wx_pay = $wx_pay->findByOutTradeNo($message['out_trade_no']);

                $easy_wechat_factory = EasyWechatFactory::payment($row_wx_pay['pay_type']);
                $result = $easy_wechat_factory->order->queryByOutTradeNumber($message['out_trade_no']);

                if (isset($result['trade_state']) && $result['trade_state'] == WxPay::SUCCESS) {
                    //证明是成功交易了
                    $wx_pay = new WxPayLogic();
                    $res = $wx_pay->whenPaySuccess($message);
                    if ($res) {
                        event('dbCommit');
                        return true;
                    } else {
                        event('dbRollback');
                        $fail("处理支付回调失败");
                    }
                } else {
                    event('dbRollback');
                    $fail("非已付款成功订单");
                }
                event('dbRollback');
            } catch (\Exception $e) {
                event('dbRollback');
                $fail($e->getMessage());
            }
        });
        $response->send();
    }

    // 微信退款回调
    public function wxRefundNotify(Request $request)
    {
        $input = XmlKit::xmlDecode(file_get_contents("php://input"));
        $easyWechat = EasyWechatFactory::payment(array_merge(EasyWechatFactory::getConfig("pay"), [
            'appid' => $input['appid'],
        ]));
        $response = $easyWechat->handleRefundedNotify(function ($message, $reqInfo, $fail) {
            try {
                $wx_refund = new WxRefund();
                $res = $wx_refund->whenRefundSuccess($reqInfo);
                if ($res) {
                    event('dbCommit');
                    return true;
                } else {
                    event('dbRollback');
                    $fail('处理微信退款失败');
                }
            } catch (\Exception $e) {
                event('dbRollback');
                $fail($e->getMessage());
            }
        });
        $response->send();
    }

    // 支付宝支付回调
    public function alipay_notify_action(Request $request)
    {
        $easy_alipay_factory = EasyAlipayFactory::instance();
        $data = $request->param();
        $data['fund_bill_list'] = htmlspecialchars_decode(html_entity_decode($data['fund_bill_list']));
        $verify = $easy_alipay_factory::payment()->common()->verifyNotify($data);

        if ($verify) {
            // 验签正确
            $zfb_pay = new ZfbPay();

            $res = $zfb_pay->whenPaySuccess($data);
            if ($res) {
                event('dbCommit');
                echo "success";
            }
            else {
                event('dbRollback');
                echo "fail";
            }
        }
        else {
            event('dbRollback');
            echo "fail";
        }
        event('dbRollback');
    }
}