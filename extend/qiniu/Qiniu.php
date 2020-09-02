<?php

namespace qiniu;

use Qiniu\Auth;

class Qiniu
{
    // 七牛配置
    const ACCESS_KEY = '';
    const SECRET_KEY = '';
    const BUCKET_NAME = '';
    const DOMAIN = '';

    public function upToken($bucket = self::BUCKET_NAME, $key = null, $expire = 3600, $policy = null, $strictPolicy = true)
    {
        $auth = new Auth(self::ACCESS_KEY, self::SECRET_KEY);
        $token = $auth->uploadToken($bucket, $key, $expire, $policy, $strictPolicy);
        return $token;
    }

}