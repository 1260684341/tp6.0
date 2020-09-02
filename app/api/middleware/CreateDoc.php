<?php

namespace app\api\middleware;


use app\common\kit\HttpKit;

class CreateDoc
{
    protected $_code = 0;
    protected $_msg = "OK";
    protected $_data = [];


    public function handle($request, \Closure $next)
    {
        $response = $next($request);
        return $response;
    }

    public function end(\think\Response $response)
    {
        $with_doc = request()->param('with_doc/b', false);
        if (request()->ip() == "127.0.0.1" && $with_doc) {

            $doc_path = request()->param('doc_path/s');
            $doc_title = request()->param('doc_title/s');
            $doc_describe = request()->param('doc_describe/s');
            $doc_note = request()->param('doc_note/s');
            $doc_sort = request()->param('doc_sort/s');

            $params_name = include app()->getBasePath() . 'common/doc/alias.php';

            $method = request()->method();
            $request_url = "{host}" . request()->baseUrl();

            // 头部
            $headers = request()->header();
            $ignore_headers = [
                'accept-language',
                'accept-encoding',
                'origin',
                'accept',
                'user-agent',
                'cache-control',
                'content-length',
                'connection',
                'host',
                'postman-token'
            ];
            $header = "";
            if (is_array($headers)) {
                $header = "";
                foreach ($headers as $key => $value) {
                    if (in_array($key, $ignore_headers)) {
                        continue;
                    }
                    if (empty($value)) {
                        $req = "非必须";
                    }
                    else {
                        $req = "必选";
                    }
                    $type = gettype($value);
                    $header .= "* @header {$key} {$req} {$type} {$value} ";
                }
            }

            // 请求实体
            $params = request()->param();
            $ignore_params = [
                'doc_path',
                'doc_title',
                'with_doc',
                'doc_describe',
                'doc_note',
                'doc_sort',
            ];
            $param = "";
            if (is_array($params)) {
                foreach ($params as $key => $value) {
                    if (in_array($key, $ignore_params)) {
                        continue;
                    }
                    if (empty($value)) {
                        $req = "非必须";
                    }
                    else {
                        $req = "必选";
                    }
                    $type = gettype($value);
                    $name = $params_name['body'][$key] ?? '？？';
                    $param .= "* @param {$key} {$req} {$type} {$name} ";
                }
            }

            // 返回内容
            $return = json_encode($response->getData(), JSON_UNESCAPED_UNICODE);

            // 返回内容解析
            $return_params = array_unique($this->parse_return_params($response->getData(), $params_name));
            $return_param = '';
            foreach ($return_params as $key => $value) {
                $return_param .= $value;
            }

            $showdoc = config('app.showdoc');
            $url = $showdoc['url'];
            $api_key = $showdoc['api_key'];
            $api_token = $showdoc['api_token'];

            // 只有本地开发人员才能生成doc
            $content = "/** 
               * showdoc 
               * @catalog {$doc_path} 
               * @title {$doc_title} 
               * @description {$doc_describe} 
               * @method {$method} 
               * @url {$request_url} 
               {$header}
               {$param}
               * @return {$return} 
               {$return_param}
               * @remark {$doc_note} 
               * @number {$doc_sort}*/";

            HttpKit::post($url, [
                'from' => 'shell',
                'api_key' => $api_key,
                'api_token' => $api_token,
                'content' => $content,
            ]);
        }
    }


    /**
     * 解析返回内容
     * @param $return_params
     * @return array
     */
    public function parse_return_params($return_params, $params_name, $lst_return_params = []) :array
    {
        foreach ($return_params as $key => $value) {
            if (is_array($value)) {
                $this->parse_return_params($value, $params_name, $lst_return_params);
            }
            else {
                $type = gettype($value);
                $name = $params_name['response'][$key] ?? '？？';
                $lst_return_params[] = "* @return_param {$key} {$type} {$name} ";
            }
        }
        return $lst_return_params;
    }


}