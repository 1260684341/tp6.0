<?php

namespace app\admin\controller;
use app\admin\base\BaseController;
use app\admin\base\Request;
use app\common\logic\AdminUser;

class Auth extends BaseController
{

    protected $whiteList = [
        'login'
    ];


    public function login(Request $request)
    {
        $account = $request->param('account');
        $password = $request->param('password');
        $admin_user = new AdminUser();
        $result = $admin_user->login($account, $password);
        return [
            'token' => $result['token'],
            'obj_admin_user' => $result['obj_admin_user'],
        ];
    }

    public function logout(Request $request)
    {
        return [];
    }

}