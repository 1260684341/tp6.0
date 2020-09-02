<?php

namespace app\common\logic;

use app\common\base\BaseLogic;
use app\common\model\User as UserModel;
use app\common\model\Wallet;
use app\common\model\VipUser;
use app\common\model\Vip;

class User extends BaseLogic
{

    public function createInviteCode()
    {
        $user = new UserModel();
        $invite_code = $user
            ->lock(true)
            ->max('invite_code');
        if (empty($invite_code)) {
            $invite_code = 0;
        }
        $invite_code = $invite_code + 1;
        $strlen = strlen($invite_code);
        for ($i = 7; $i > $strlen; $i--) {
            $invite_code = '0'. $invite_code;
        }
        return $invite_code;
    }

    //获取用户数据
    public function list($user_id)
    {
        $model_user = new UserModel();
        $rows_user = $model_user->getUserInfo($user_id);
        if (empty($rows_user)) {
            return [];
        }
        return $rows_user;
    }
    //获取余额
    public function wallet($user_id)
    {
        $wallet = new Wallet();
        $row_balance = $wallet->getWallet($user_id);
        if (empty($row_balance)) {
            return [];
        }
        return  $row_balance;
    }
    //是否为vip
    public function isVip($user_id)
    {
        $vip_user = new VipUser();
        $is_vip = $vip_user->findUserVip($user_id);
        $vip = new Vip();
        $vip_name = $vip->vipName($is_vip["vip_id"]);
        if (empty($vip_name)) {
            return [];
        }
        return $vip_name;

    }

    //资料修改
    public function datumEdit($user_id, array $data)
    {
        $model_user = new UserModel();
        $model_user->update([
            'header_img' => $data['header_img'],
            'name' => $data['name'],
            'signature' => $data['signature'],
            'phone' => $data['phone'],
            'email' => $data['email'],
            'sex' => $data['sex'],
        ], ['id' => $user_id]);
    }
}