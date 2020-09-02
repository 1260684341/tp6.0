<?php

namespace app\common\model;

use app\common\base\BaseModel;

class ZfbPay extends BaseModel {


    const PAY_STATUS_WAITING = 0; //待支付
    const PAY_STATUS_SUCCESS = 1; //支付成功
    const PAY_STATUS_FAIL = 2;    //支付失败

    public function getByOutTradeNo($out_trade_no)
    {
        return $this->where('out_trade_no', $out_trade_no)->find();
    }

}