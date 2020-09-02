<?php
namespace app\common\kit;


class VerifyKit
{

    /***
     * 检验身份证
     * @param $id_card
     * @return bool
     */
    public static function isIdCard($id_card){
        if(strlen($id_card)==18){
            return self::idcardChecksum18($id_card);
        }elseif((strlen($id_card)==15)){
            $id_card=self::idcard_15to18($id_card);
            return self::idcardChecksum18($id_card);
        }else{
            return false;
        }
    }

    // 计算身份证校验码，根据国家标准GB 11643-1999
    public static function idcardVerifyNumber($idcard_base){
        if(strlen($idcard_base)!=17){
            return false;
        }
        //加权因子
        $factor=array(7,9,10,5,8,4,2,1,6,3,7,9,10,5,8,4,2);
        //校验码对应值
        $verify_number_list=array('1','0','X','9','8','7','6','5','4','3','2');
        $checksum=0;
        for($i=0;$i<strlen($idcard_base);$i++){
            $checksum += substr($idcard_base,$i,1) * $factor[$i];
        }
        $mod=$checksum % 11;
        $verify_number=$verify_number_list[$mod];
        return $verify_number;
    }

    // 将15位身份证升级到18位
    public static function idcard_15to18($idcard){
        if(strlen($idcard)!=15){
            return false;
        }else{
            // 如果身份证顺序码是996 997 998 999，这些是为百岁以上老人的特殊编码
            if(array_search(substr($idcard,12,3),array('996','997','998','999')) !== false){
                $idcard=substr($idcard,0,6).'18'.substr($idcard,6,9);
            }else{
                $idcard=substr($idcard,0,6).'19'.substr($idcard,6,9);
            }
        }
        $idcard=$idcard.self::idcardVerifyNumber($idcard);
        return $idcard;
    }

    // 18位身份证校验码有效性检查
    public static function idcardChecksum18($idcard){
        if(strlen($idcard)!=18){
            return false;
        }
        $idcard_base=substr($idcard,0,17);
        if(self::idcardVerifyNumber($idcard_base)!=strtoupper(substr($idcard,17,1))){
            return false;
        }else{
            return true;
        }
    }


    /**
     * 检验手机号
     */
    public static function isPhone($phone)
    {
        if (empty($phone)) {
            return false;
        }
        return preg_match('/^13[\d]{9}$|^14[\d]{9}$|^15[\d]{9}$|^16[\d]{9}$|^17[\d]{9}$|^18[\d]{9}$|^19[\d]{9}$/', $phone) ? true : false;
    }

    /**
     * 校验固话
     * @param $str
     * @return bool
     */
    public static function isTel($str)
    {
        return (preg_match("/^(((d{3}))|(d{3}-))?((0d{2,3})|0d{2,3}-)?[1-9]d{6,8}$/",$str)) ? true : false;
    } //使用方法


    /**
     * 校验日期有效性
     * @param $date 日期
     * @param bool $is_future 允许是未来的日期 默认不许
     */
    public static function isDate($date, $formats = array("Y-m-d H:i:s", "Y/m/d H:i:s") , $is_future = false)
    {
        if (empty($date)) {
            return false;
        }
        $unixTime = strtotime($date);
        if (!$unixTime) {
            return false;
        }

        if (!$is_future) {
            if ($unixTime > time()) {
                return false;
            }
        }

        foreach ($formats as $format) {
            if (date($format, $unixTime) != $date) {
                return false;
            }
        }

        return true;

    }


    //校验数字
    public static function isNumber($num)
    {
        if(preg_match("/^\d*$/", $num)) {
            return true;
        }
        else {
            return false;
        }
    }


}