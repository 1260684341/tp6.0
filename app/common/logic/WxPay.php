<?php

namespace app\common\logic;

use app\common\base\BaseLogic;
use app\common\interfaceObj\ITrade;
use app\common\kit\CommonKit;
use app\common\kit\StringKit;
use app\common\model\WxPay as WxPayModel;
use wechat\EasyWechatFactory;

class WxPay extends BaseLogic
{


    private $expire_min = 5;

    /**
     * @param ITrade $trade
     * @param string $out_trade_no 商户订单号
     * @param int $total_fee 支付金额
     * @param string $pay_type 支付类型，参考EasyWechatFactory里面的类型 pay_type
     * @param array $datas ['openid' => '', 'product_id' => '', 'attach' => '' ... ] 其他微信支付非必传参数
     * @return array|string
     */
    public function unifyPay(ITrade $trade, $out_trade_no, $total_fee, $pay_type = EasyWechatFactory::PAY_TYPE_GZH, array $datas = [])
    {
        $data = [
            'pay_type' => $pay_type,
            'callback_class' => get_class($trade),
            'out_trade_no' => $out_trade_no,
            'time_start' => date("YmdHis", time()),
            'time_expire' => date("YmdHis", strtotime("+{$this->expire_min}min")),
            'body' => $datas['body'] ?? '商城下单',
            'total_fee' => StringKit::changeToFee($total_fee),
            'spbill_create_ip' => CommonKit::ip(),
            'trade_type' => EasyWechatFactory::getTradeType($pay_type),
        ];
        $data = array_merge($data, $datas);
        $model_wxpay = new WxPayModel();
        $model_wxpay->save($data);

        unset($data['pay_type']);
        unset($data['callback_class']);

        $easy_wechat_factory = EasyWechatFactory::payment($pay_type);
        $result = $easy_wechat_factory->order->unify($data);
        if (isset($result['return_code']) && $result['return_code'] == WxPayModel::FAIL) {
            e($result['return_msg']);
        }

        if (isset($result['result_code']) && $result['result_code'] == WxPayModel::FAIL) {
            e($result['err_code_des']);
        }
        if (isset($result['prepay_id'])) {
            return $easy_wechat_factory->jssdk->bridgeConfig($result['prepay_id'], false);
        }
        else {
            e('微信支付未知错误');
        }
    }


    public function whenPaySuccess($response)
    {
        try {
            $model_wxpay = new WxPayModel();
            $row_wx_pay = $model_wxpay->findByOutTradeNo($response['out_trade_no']);
            // 修改支付状态
            $res = $model_wxpay->where('id', $row_wx_pay['id'])->update([
                'transaction_id' => $response['transaction_id'],
                'pay_status' => WxPayModel::PAY_STATUS_SUCCESS,
                'pay_time' => StringKit::getCurrentDatetime(),
            ]);

            if ($res === false) {
                return false;
            }

            $callback_class = new $row_wx_pay['callback_class']();
            $res = $callback_class->whenPaySuccess($row_wx_pay['out_trade_no']);
            return $res;
        }
        catch (\Exception $e) {
            file_put_contents('WxPayCallBackFail.log', $e->getMessage());
            return false;
        }

    }


}