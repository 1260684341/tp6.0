<?php

namespace app\api\middleware;


class BaseResponse
{
    private $_code = 0;
    private $_msg = "OK";
    private $_data = [];

    public function handle($request, \Closure $next)
    {
        $cors_headers = [
            'Access-Control-Allow-Origin' => "*",
            'Access-Control-Allow-Headers' => "*",
            'Access-Control-Allow-Methods' => "*",
            'Content-Type' => 'text/plain; charset=UTF-8',
            'Cache-Control' => "no-store, must-revalidate",
            'Pragma' => "no-cache",
        ];

        try {

            $response = $next($request);
            if ($request->Method() == "OPTIONS") {
                return json([
                    'code' => $this->_code,
                    'msg' => $this->_msg,
                    'data' => $this->_data
                ], 200, $cors_headers);
            }

            // 添加中间件执行代码
            $this->_code = $response->getCode();
            if ($this->_code != 200) {
                $this->_msg = is_string($response->getData()) ? $response->getData() : '';
                $this->_data = [];
            } else {
                $this->_code = 0;
                $this->_msg = '';
                $this->_data = $this->safe_array_for_json_output($this->object_to_array($response->getData()));
            }

            if (empty($this->_data)) {
                $this->_data = [];
            }

            return json([
                'code' => $this->_code,
                'msg' => $this->_msg,
                'data' => $this->_data
            ], 200, $cors_headers);
        } catch (\Exception $e) {
            return json([
                'code' => $this->_code,
                'msg' => $e->getMessage(),
                'data' => $this->_data
            ], 505, $cors_headers);
        }
    }

    public function end(\think\Response $response)
    {
        if ($this->_code == 0) {
            event('dbCommit'); // 提交数据
        }
        else {
            event('dbRollback'); // 回滚数据
        }
    }


    final protected function safe_array_for_json_output($arr)
    {
        if (is_array($arr)) {
            // 用户表
            unset($arr['password']);
            unset($arr['salt']);
            if (isset($arr['email'])) {
                $arr['email'] = mb_substr($arr['email'], 0, 3) . "***" . mb_substr($arr['email'], -3);
            }
            if (isset($arr['account'])) {
                $arr['account'] = mb_substr($arr['account'], 0, 3) . "***" . mb_substr($arr['account'], -3);
            }
            if (isset($arr['phone'])) {
                $arr['phone'] = mb_substr($arr['phone'], 0, 3) . "***" . mb_substr($arr['phone'], -3);
            }
            foreach ($arr as &$value) {
                if (is_array($value)) {
                    $value = $this->safe_array_for_json_output($value);
                }
            }
        }
        return $arr;
    }


    public function object_to_array($obj)
    {
        // -。- 方（懒）便（懒）快（懒）捷
        return json_decode(json_encode($obj), true);
    }


}