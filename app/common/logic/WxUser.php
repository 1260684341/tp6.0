<?php

namespace app\common\logic;
use app\common\base\BaseLogic;
use app\common\kit\StringKit;
use app\common\model\WxUser as WxUserModel;
use app\common\model\User as UserModel;

class WxUser extends BaseLogic
{

    public function registerByPhone($phone, $data)
    {
        $model_user = new UserModel();
        $row_user = $model_user->where('phone', $phone)->find();
        if (empty($row_user)) {

            if (!empty($data['invite_code'])) {
                $row_parent_user = $model_user->where('invite_code', $data['invite_code'])->find();
                if (empty($row_parent_user)) {
                    e('parent_user_not_exist');
                }
                $parent_id = $row_parent_user['id'];
            }
            else {
                $parent_id = '';
            }

            // 注册普通用户
            $header_img = $data['header_img'] ?? UserModel::defaultHeaderImg();
            $name = $data['name'] ?? $phone;
            $user = new User();
            $model_user->save([
                'parent_id' => $parent_id,
                'name' => $name,
                'phone' => $phone,
                'invite_code' => $user->createInviteCode(),
                'wechat_num' => $data['wechat_num'] ?? '',
                'salt' => $data['salt'] ?? '',
                'password' => $data['password'] ?? '',
                'true_name' => $data['true_name'] ?? '',
                'id_card' => $data['id_card'] ?? '',
                'sex' => $data['sex'] ?? '',
                'header_img' => $header_img,
                'status' => UserModel::STATUS_NORMAL,
                'role' => UserModel::ROLE_USER,
                'birthday' => $data['birthday'] ?? StringKit::getCurrentDatetime(),
            ]);
            $user_id = $model_user->getLastInsID();

            // 绑定微信用户
            $model_wx_user = new WxUserModel();
            $model_wx_user->save([
                'user_id' => $user_id,
                'nickname' => $data['name'] ?? '',
                'headimgurl' => $header_img,
                'gzh_openid' => $data['gzh_openid'] ?? '',
                'xcx_openid' => $data['xcx_openid'] ?? '',
                'unionid' => $data['unionid'] ?? '',
                'subscribe' => $data['subscribe'] ?? '',
                'sex' => $data['sex'] ?? WxUserModel::SEX_MAN,
                'province' => $data['province'] ?? '',
                'city' => $data['city'] ?? '',
                'country' => $data['country'] ?? '',
                'subscribe_time' => $data['subscribe_time'] ?? '',
            ]);
            $row_user = $model_user->findById($user_id);
        }
        return $row_user;
    }



    public function getWxUserByOpenid($openid)
    {
        $wx_user = new WxUserModel();
        $row_wx_user = $wx_user->findByOpenid($openid);
        return $row_wx_user;
    }

    public function getUserByOpenid($openid)
    {
        $row_wx_user = $this->getWxUserByOpenid($openid);
        $user_id = $row_wx_user['user_id'];
        $row_user = null;
        if (!empty($user_id)) {
            $model_user = new UserModel();
            $row_user = $model_user->findById($user_id);
        }

        return $row_user;
    }


    public function bindWxUser($user_id, $wx_user_id)
    {
        $model_wx_user = new WxUserModel();
        return $model_wx_user->update(['user_id' => $user_id], ['id' => $wx_user_id]);
    }
}