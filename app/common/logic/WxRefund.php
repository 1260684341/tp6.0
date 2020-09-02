<?php
namespace app\common\logic;
use app\common\base\BaseLogic;
use app\common\interfaceObj\ITrade;
use app\common\kit\HttpKit;
use app\common\kit\StringKit;
use app\common\model\WxRefund as WxRefundModel;
use app\common\model\WxPay as WxPayModel;
use wechat\EasyWechatFactory;

class WxRefund extends BaseLogic
{


    public function unifyRefund(ITrade $trade, $row_order, $refund_apply_id, $refund_fee, $reason , $order_type = WxRefundModel::ORDER_TYPE_COMMON_ORDER)
    {
        // 获取退款单
        $model_refund_apply = new WxRefundModel();
        $row_refund_apply = $model_refund_apply->findById($refund_apply_id);

        $order_id = $row_refund_apply['order_id'];
        $model_wx_pay = new WxPayModel();
        $row_wx_pay = $model_wx_pay->findByOutTradeNo($row_order['out_trade_no']);

        if (empty($row_wx_pay) || $row_wx_pay['pay_status'] != WxPayModel::PAY_STATUS_SUCCESS) {
            e('订单未交易成功');
        }

        $pay_type = $row_wx_pay['pay_type'];
        $out_refund_no = $this->createOutRefundNo();
        $notify_url = HttpKit::host() . "/index.php/notify/Index/wxrefund";
        $data = [
            'pay_type' => $pay_type,
            'order_type' => $order_type,
            'callback_class' => get_class($trade),
            'wx_pay_id' => $row_wx_pay['id'],
            'order_id' => $order_id,
            'refund_apply_id' => $refund_apply_id,
            'refund_status' => WxRefundModel::REFUND_STATUS_WITING,
            'appid' => $row_wx_pay['appid'],
            'mch_id' => $row_wx_pay['mch_id'],
            'sign_type' => $row_wx_pay['sign_type'],
            'transaction_id' => $row_wx_pay['transaction_id'],
            'out_trade_no' => $row_wx_pay['out_trade_no'],
            'out_refund_no' => $out_refund_no,
            'total_fee' => $row_wx_pay['total_fee'],
            'fee_type' => $row_wx_pay['fee_type'],
            'refund_fee' => $refund_fee,
            'refund_fee_type' => $row_wx_pay['fee_type'],
            'refund_desc' => $reason,
            'notify_url' => $notify_url,
        ];

        $this->save($data);

        $config = [
            'notify_url' => $notify_url,
        ];

        $easy_wechat_factory = EasyWechatFactory::payment($pay_type);
        $result = $easy_wechat_factory->refund->byOutTradeNumber($data['out_trade_no'], $out_refund_no, $data['total_fee'], $refund_fee, $config);

        if (isset($result['return_code']) && $result['return_code'] == 'FAIL') {
            e($result['return_msg']);
        }

        if (isset($result['result_code']) && $result['result_code'] == 'FAIL') {
            e($result['err_code_des']);
        }

        return true;
    }


    public function whenRefundSuccess($response)
    {
        $out_refund_no = $response['out_refund_no'];
        $row_wx_refund = $this->findByOutRefundNo($out_refund_no);

        $res = $this->save([
            'refund_status' => WxRefundModel::REFUND_STATUS_SUCCESS,
            'refund_time' => StringKit::getCurrentDatetime(),
        ], ['id' => $row_wx_refund['id']]);

        if ($res === false) {
            return false;
        }

        $callback_class = new $row_wx_refund['callback_class']();
        $res = $callback_class->whenRefundSuccess($row_wx_refund['out_trade_no']);
        return $res;
    }


    private function createOutRefundNo()
    {
        return "refund_" . date('YmdHis') . rand(10, 99);
    }


}