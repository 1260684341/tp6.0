<?php

namespace app\common\kit;

class HttpKit
{

    static public function getUrl()
    {
        return self::host() . $_SERVER["REQUEST_URI"];
    }

    static public function getUri($url)
    {
        if (($index = strpos($url, '?')) === false) {
            return $url;
        }
        return substr($url, 0, $index);
    }

    static public function host()
    {
        $url = 'http';

        if (isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == "on") {
            $url .= "s";
        }
        $url .= "://";

        if ($_SERVER["SERVER_PORT"] != "80" && $_SERVER["SERVER_PORT"] != "443") {
            $url .= $_SERVER["HTTP_HOST"] . ":" . $_SERVER["SERVER_PORT"];
        } else {
            $url .= $_SERVER["HTTP_HOST"];
        }
        return $url;
    }

    static public function post($url, $param, $headers = null)
    {
        $oCurl = curl_init();
        if (stripos($url, "https://") !== false) {
            curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($oCurl, CURLOPT_SSL_VERIFYHOST, false);
        }
        if (is_string($param)) {
            $strPOST = $param;
        } else {
            $strPOST = http_build_query($param);
        }
        curl_setopt($oCurl, CURLOPT_URL, $url);
        curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($oCurl, CURLOPT_POST, true);
        curl_setopt($oCurl, CURLOPT_POSTFIELDS, $strPOST);

        if (!empty($headers)) {
            curl_setopt($oCurl, CURLOPT_HTTPHEADER, $headers);
        }

        $sContent = curl_exec($oCurl);
        $aStatus = curl_getinfo($oCurl);

        if (curl_errno($oCurl) > 0) {
            file_put_contents('curl_error.log', curl_errno($oCurl) . " - " . curl_error($oCurl));
        }
        curl_close($oCurl);
        if (intval($aStatus["http_code"]) == 200) {
            return $sContent;
        } else {
            $time = StringKit::get_current_datetime();
            file_put_contents($time . "curl_error_html.html", $sContent);
            return false;
        }
    }


    /**
     *   curl获取页面结果
     */
    static function get($url)
    {
        //初始化
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_TIMEOUT, 500);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);

        //设置url
        curl_setopt($curl, CURLOPT_URL, $url);
        //设置返回获取的输出为文本流
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        //执行命令
        $result = curl_exec($curl);
        if (curl_errno($curl) > 0) {
            file_put_contents('curl_error.log', curl_errno($curl) . " - " . curl_error($curl));
        }
        //关闭URL请求
        curl_close($curl);
        return $result;
    }

}