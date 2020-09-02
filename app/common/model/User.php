<?php
namespace app\common\model;

use app\common\base\BaseModel;
use app\common\kit\HttpKit;

class User extends BaseModel
{

	const SEX_MAN = 1;		// 男人
	const SEX_WOMAN = 2;	// 女人

    const STATUS_NORMAL = 1;
    const ROLE_USER = 1;

    public static function defaultHeaderImg()
    {
        return HttpKit::host() . 'static/default_header.png';
    }

    public function getByPhone($phone)
    {
        return $this->where('phone', $phone)
            ->find();
    }
    public function getUserInfo($user_id)
    {
        return $this
            ->where('id', $user_id)
            ->find();
    }
    public function getUserName($user_id)
    {
        return $this
            ->where('id', $user_id)
            ->value('name');
    }
}