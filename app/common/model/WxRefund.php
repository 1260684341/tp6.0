<?php
namespace app\common\model;

use app\common\base\BaseModel;

class  WxRefund extends BaseModel
{
    const TRADE_TYPE_JSPAI = 'JSAPI';
    const TRADE_TYPE_NATIVE = 'NATIVE';
    const TRADE_TYPE_APP = 'APP';
    const TRADE_TYPE_OTHER = 'OTHER';

    const ORDER_TYPE_COMMON_ORDER = 0;// 普通订单退款
    const ORDER_TYPE_GROUP_BUY_ORDER = 1;// 团购订单退款
    const ORDER_TYPE_BARGAIN_ORDER = 2;// 砍价订单退款

    const REFUND_STATUS_WITING = 0; //待退款
    const REFUND_STATUS_SUCCESS = 1; //退款成功
    const REFUND_STATUS_FAIL = 2;    //退款失败

    public function findByOutRefundNo($out_refund_no)
    {
        return $this->byOutRefundNo($out_refund_no)->find();
    }

    public function byOutRefundNo($out_refund_no)
    {
        return $this->where('out_refund_no', $out_refund_no);
    }
}