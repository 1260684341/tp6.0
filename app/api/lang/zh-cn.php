<?php
return [
    // 系统错误
    'undefined_error' => '未定义错误',
    'token_error' => '签名错误',
    'auth_expire' => '登陆超时',

    // 登陆注册
    'openid_error' => '获取openid失败',
    'mpwechat_get_phone_fail' => '授权获取手机号失败',
    'mpwechat_not_register' => '小程序账号未注册，请使用手机号登陆',

    // 收藏
    'collect_course_not_exist' => '收藏的课程不存在',
    'collect_album_not_exist' => '收藏的天籁不存在',
    'collect_product_not_exist' => '收藏的商品不存在',

    // 课程
    'course_need_vip' => '该视频需要VIP才能观看',
    'course_need_pay' => '该视频需要购买才能观看',

    // vip
    'vip_not_exist' => 'vip不存在',


    //地址
    'address_id_is_required' => '地址id不能为空',
    'receiver_is_required' => '收件人不能为空',
    'receiver_phone_is_required' => '收件人手机号不能为空',
    'receiver_address_is_required' => '收件人详细地址不能为空',
    'add_code_is_required' => '地区编码不能为空',
    'add_code_is_not_found' => '地区编码找不到',
    'receiver_phone_format_is_error' => '手机格式错误',

    //购物车
    'product_id_is_required' => '商品id不能为空',
    'product_stock_is_insufficient' => '商品库存不足',
    'origin_price_is_required' => '商品原价不能为空',
    'price_is_required' => '商品售价不能为空',
    'buy_num_is_required' => '购买的数量不能为空',

    //订单
    'product_sku_name_is_required' => '订单规格不能为空',
    'buy_num_less_than_zero' => '数量不能小于0',

    //教师
    'name_is_required' => '教师名字不能为空',
    'teacher_introduce_is_required' => '教师简述不能为空',
    'header_img_is_required' => '头像不能为空',
    'background_is_required' => '背景资源不能为空',

    //banner
    'src_is_required' => '图片地址不能为空',
    'source_type_is_required' => '资源类型不能为空',
];