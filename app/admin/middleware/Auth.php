<?php
namespace app\admin\middleware;

use app\common\kit\JwtKit;

class Auth
{

    public function handle($request, \Closure $next)
    {
        $whiteList = $request->whiteList;
        if (empty($request->action())) {
            return json(['msg' => "啥玩意？"]);
        }

        if (!in_array($request->action(), $whiteList) && !in_array("*", $whiteList)) {

            // $payload = ['user_id' => 1, 'name' => "用户名称", 'header_img' => 'xxx'];
            // request_code标识一个请求设备
            // $headers = $request->header();
            $token = $request->header()['token'] ?? '';
            if (empty($token)) {
                e('token_error');
            }
            else {
                $payload = JwtKit::verifyToken($token);
                if (!$payload) {
                    e('token_error');
                }
                // todo 判断过期时间

                // todo 在数据库判断 request_code

                // todo 每隔半个小时刷新token
            }

            $request->payload = $payload;
        }
        else {
            $request->payload = [];
            $token = $request->header()['token'] ?? '';
            if (!empty($token)) {
                $payload = JwtKit::verifyToken($token);
                if ($payload) {
                    $request->payload = $payload;
                }
            }
        }
        $response = $next($request);
        return $response;
    }

}