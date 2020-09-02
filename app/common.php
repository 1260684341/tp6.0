<?php
// 应用公共文件
use app\api\exception\ApiException;
use think\Response;

if (!function_exists('e')) {

    function e($msg): Response
    {
        throw new ApiException($msg);
    }
}