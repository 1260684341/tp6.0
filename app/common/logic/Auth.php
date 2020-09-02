<?php

namespace app\common\logic;
use app\common\base\BaseLogic;
use app\common\kit\JwtKit;
use app\common\model\User as UserModel;
use app\common\model\UserToken;
use think\facade\Validate;
use wechat\EasyWechatFactory;

class Auth extends BaseLogic
{

    public function mpWeixinAuthLogin($input, $request_code)
    {
        if (empty($input['code'])) {
            e('openid_error');
        }

        $wechat_app = EasyWechatFactory::miniProgram();
        $result = $wechat_app->auth->session($input['code']);

        if (empty($result['openid'])) {
            e('openid_error');
        }

        $model_wx_user = new WxUser();
        if (isset($result['unionid']) && !empty($result['unionid'])) {
            // 根据 unionid 查询 wx_user 对应的用户
            $row_wx_user = $model_wx_user->getByUnionId($result['unionid']);
        }

        if (empty($row_wx_user)) {
            // 查找 wx user 对应的用户
            $row_wx_user = $model_wx_user->getByXcxOpenId($result['openid']);
            if (empty($row_wx_user) || empty($row_wx_user['user_id'])) {
                // 未注册
                e('mpwechat_not_register');
            }
            $model_user = new UserModel();
            $row_user = $model_user->findById($row_wx_user['user_id']);
        }
        else {
            if (empty($row_wx_user['user_id'])) {
                e('mpwechat_not_register');
            }
            $model_user = new UserModel();
            $row_user = $model_user->findById($row_wx_user['user_id']);
        }
        return $this->login($row_user['id'], $request_code);
    }

    public function login($user_id, $request_code)
    {
        $user = new UserModel();
        $row_user = $user->findById($user_id);
        if (empty($row_user)) {
            return false;
        }
        $payload['obj_user'] = [
            'id' => $row_user['id'],
            'parent_id' => $row_user['parent_id'],
            'family_path' => $row_user['family_path'],
            'name' => $row_user['name'],
            'phone' => $row_user['phone'],
            'invite_code' => $row_user['invite_code'],
            'wechat_num' => $row_user['wechat_num'],
            'true_name' => $row_user['true_name'],
            'id_card' => $row_user['id_card'],
            'sex' => $row_user['sex'],
            'header_img' => $row_user['header_img'],
            'status' => $row_user['status'],
            'role' => $row_user['role'],
            'birthday' => $row_user['birthday'],
        ];
        $payload['exp'] = time() + 7200;
        $token = JwtKit::getToken($payload);

        // 存入 Token
        $model_user_token = new UserToken();
        $model_user_token->saveToken($row_user['id'], $request_code);

        return [
            'obj_token' => $token,
            'obj_user' => $row_user
        ];
    }

    public function register_by_mpweixin_phone($input)
    {
        $validate = Validate::rule([
            'code'  => 'require',
            'iv' => 'require',
            'encrypted' => 'require',
        ]);

        if (!$validate->check($input)) {
            e($validate->getError());
        }

        $wechat_app = EasyWechatFactory::miniProgram();
        $result = $wechat_app->auth->session($input['code']);

        if (empty($result['openid'])) {
            e('openid_error');
        }

        if (empty($result['session_key'])) {
            e('mpwechat_get_phone_fail');
        }

        // 解密手机号
        $phone = $wechat_app->encryptor->decryptData($result['session_key'], $input['iv'], $input['encrypted']);
        $phone = $phone['phoneNumber'];

        $wx_user = new WxUser();
        $input['xcx_openid'] = $result['openid'];
        $input['unionid'] = $result['unionid'] ?? '';
        $row_user = $wx_user->registerByPhone($phone, $input);
        return $row_user;
    }

}