<?php
namespace app\api\exception;

use think\Response;
use Throwable;

/**
 * 应用异常处理类
 */
class ApiException extends \think\Exception
{

    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        event('dbRollback');
        parent::__construct($message, $code, $previous);
    }

    public static function response($request, Throwable $e): Response
    {
        event('dbRollback');
        // 根据message获取 各种语言的“错误提示”
        $msg = lang($e->getMessage());
        if (empty($msg)) {
            $msg = $e->getMessage();
        }

        $undefined_error_code = -999;
        // 根据message获取 获取“错误码”
        $errorCodes = include "exception.php";
        $code = $errorCodes[$e->getMessage()] ?? $undefined_error_code;
        if (empty($code)) {
            $code = $e->getCode();
        }
        return response($msg, $code, [], 'JSON');
    }
}
