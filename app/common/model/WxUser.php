<?php
namespace app\common\model;

use app\common\base\BaseModel;

class WxUser extends BaseModel
{
    const SEX_UNKNOWN = 0;
    const SEX_MAN = 1;
    const SEX_WOMAN = 2;

    public function findByOpenid($openid)
    {
        return $this->openid($openid)->find();
    }

    public function openid($openid)
    {
        $this->where('gzh_openid|xcx_openid', $openid);
        return $this;
    }

    public function getByUnionId($unionid)
    {
        return $this->where('unionid', $unionid)
            ->find();
    }

    public function getByXcxOpenId($openid)
    {
        return $this->where('xcx_openid', $openid)
            ->find();
    }

    public function getByUserId($user_id)
    {
        return $this->where('user_id', $user_id)
            ->find();
    }


}