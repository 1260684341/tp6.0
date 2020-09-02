<?php
namespace app\common\exception;

use app\api\exception\ApiException;
use think\Response;
use Throwable;

/**
 * 应用异常处理类
 */
class CommonException extends \think\Exception
{

    public static function response($request, Throwable $e): Response
    {
        // 根据message获取 各种语言的“错误提示”
        if ($e instanceof ApiException) {
            $code = $e->getCode();
        }
        else {
            $code = 500;
        }
        if (!app()->isDebug()) {
            $msg = $e->getMessage();
        } else {
            $msg = "【 " . $e->getMessage() . " 】【 " . $e->getFile() . " 】【 " . $e->getLine() . " 】";
            $request_data = "【";
            $request_data .= json_encode($request->param());
            $request_data .= "】";
            $msg = $msg . $request_data;
        }

        return response($msg, $code, [], 'JSON');
    }
}
