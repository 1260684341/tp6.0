<?php
namespace app\common\model;


use app\common\base\BaseModel;

class AdminUser extends BaseModel
{

    public function findByAccount($account)
    {
        return $this->where('account', $account)
            ->find();
    }
}