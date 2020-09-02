<?php
namespace app\common\model;

use app\common\base\BaseModel;

class WxPay extends BaseModel
{

    const SUCCESS = 'SUCCESS';
    const FAIL = 'FAIL';

    const PAY_STATUS_WAITING = 0; //待支付
    const PAY_STATUS_SUCCESS = 1; //支付成功
    const PAY_STATUS_FAIL = 2;    //支付失败

    public function findByOutTradeNo($out_trade_no)
    {
        return $this->byOutTradeNo($out_trade_no)->find();
    }

    public function byOutTradeNo($out_trade_no)
    {
        return $this->where('out_trade_no', $out_trade_no);
    }

}