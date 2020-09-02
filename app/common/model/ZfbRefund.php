<?php
namespace app\common\model;

use app\common\base\BaseModel;

class ZfbRefund extends BaseModel
{
    const REFUND_STATUS_WITING = 0; //待退款
    const REFUND_STATUS_SUCCESS = 1; //退款成功
    const REFUND_STATUS_FAIL = 2;    //退款失败
}