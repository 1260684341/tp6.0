<?php

namespace app\admin\controller;
use app\admin\base\BaseController;

class AdminUser extends BaseController
{
    protected $whiteList = [
        'privileges' // 暂时
    ];

    public function privileges()
    {
        return [
            'permissions' => [
                'admin', 'editProduct'
            ],
        ];
    }
}