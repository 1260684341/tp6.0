<?php

namespace app\common\logic;
use app\common\base\BaseLogic;
use app\common\kit\CommonKit;
use app\common\kit\JwtKit;
use app\common\kit\StringKit;
use app\common\model\AdminLoginLog;
use app\common\model\AdminUser as AdminUserModel;

class AdminUser extends BaseLogic
{

    public function login($account, $password)
    {
        $model_admin_user = new AdminUserModel();
        $row_admin_user = $model_admin_user->findByAccount($account);
        if (empty($row_admin_user)) {
            e('admin_user_password_error');
        }

        if (!$this->checkPassword($password, $row_admin_user['salt'], $row_admin_user['password'])) {
            e('admin_user_password_error');
        }

        // 更新最后一次的登陆信息
        $row_admin_user->last_login_ip = CommonKit::ip();
        $row_admin_user->last_login_time = StringKit::getCurrentDatetime();
        $row_admin_user->save();

        // 记录登录日志
        $model_admin_login_log = new AdminLoginLog();
        $model_admin_login_log->record($row_admin_user['id'], $row_admin_user['account']);

        // 生成token
        $obj_admin_use = $row_admin_user->toArray();
        unset($obj_admin_use['password']);
        unset($obj_admin_use['salt']);
        $token = JwtKit::getToken($obj_admin_use);

        return [
            'token' => $token,
            'obj_admin_user' => $obj_admin_use,
        ];
    }

    public function checkPassword($passwd, $salt, $password)
    {
        if ($this->generatePasswordMd($passwd, $salt) == $password) {
            return true;
        }
        return false;
    }

    // 加密密码
    public function generatePasswordMd($passwd, $salt)
    {
        return md5(md5($passwd) . $salt);
    }


}