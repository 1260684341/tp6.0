<?php
namespace app\common\model;


use app\common\base\BaseModel;
use app\common\kit\CommonKit;
use app\common\kit\StringKit;

class AdminLoginLog extends BaseModel
{

    /**
     * @param $admin_user_id
     * @param $account
     * @return bool
     */
    public function record($admin_user_id, $account)
    {
        $data = [];
        $data['admin_user_id'] = $admin_user_id;
        $data['account'] = $account;
        $data['ip'] = CommonKit::ip();
        $data['login_time'] = StringKit::getCurrentDatetime();
        return $this->save($data);
    }
}