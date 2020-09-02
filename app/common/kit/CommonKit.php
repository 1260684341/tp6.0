<?php
namespace app\common\kit;

use Qiniu\Storage\UploadManager;
use Qiniu\Auth;
use QRcode;

class CommonKit
{

    // 通用生成二维码
    static public function qrcode($value, $filename) {
        require_once  root_path() . '/common/tool/qrcode.php';
        $errorCorrectionLevel = 'L';    //容错级别
        $matrixPointSize = 5;           //生成图片大小
        //生成二维码图片
        QRcode::png($value, $filename, $errorCorrectionLevel, $matrixPointSize, 2);
    }

    /**
     * 获取客户端IP地址
     * @param integer   $type 返回类型 0 返回IP地址 1 返回IPV4地址数字
     * @param boolean   $adv 是否进行高级模式获取（有可能被伪装）
     * @return mixed
     */
    public static function ip($type = 0, $adv = false)
    {
        $type      = $type ? 1 : 0;
        static $ip = null;
        if (null !== $ip) {
            return $ip[$type];
        }

        if ($adv) {
            if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                $arr = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
                $pos = array_search('unknown', $arr);
                if (false !== $pos) {
                    unset($arr[$pos]);
                }

                $ip = trim($arr[0]);
            } elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
                $ip = $_SERVER['HTTP_CLIENT_IP'];
            } elseif (isset($_SERVER['REMOTE_ADDR'])) {
                $ip = $_SERVER['REMOTE_ADDR'];
            }
        } elseif (isset($_SERVER['REMOTE_ADDR'])) {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        // IP地址合法验证
        $long = sprintf("%u", ip2long($ip));
        $ip   = $long ? array($ip, $long) : array('0.0.0.0', 0);
        return $ip[$type];
    }


    // 通用上传
    const UPLOAD_SUCCESS = 0;
    const UPLOAD_FAIL = -1;
    const UPLOAD_TYPE_PRODUCT = 1;
    const UPLOAD_TYPE_BRAND = 2;
    const UPLOAD_TYPE_CATEGORY = 3;
    const UPLOAD_TYPE_BANNER = 4;
    const UPLOAD_TYPE_SLIDESHOW = 5;
    const UPDATE_TYPE_SUNDRY = 6;	// 杂项上传
    const UPDATE_TYPE_CUSTOMIZE_AREA = 7;	// 小程序专区图片
	const UPDATE_QINIU = 1;		// 上传七牛 0为关闭 1为开启
    static public function upload($filename, $path, $ext = "jpg,png,gif,jpeg", $size = 2097152) {
        // 获取表单上传文件 例如上传了001.jpg
        $file = request()->file($filename);

        if (empty($file)) {
            return array('code' => self::UPLOAD_FAIL, 'errmsg' => "文件上传失败，请检查服务器配置", 'savename' =>"", 'filename' => "");
        }

        if (self::UPDATE_QINIU) {
        	// 获取配置
			$qiniu_config = config('qiniu');
			if (empty($qiniu_config)) {
				return array('code' => self::UPLOAD_FAIL, 'errmsg' => "文件上传失败，请检查七牛配置", 'savename' =>"", 'filename' => "");
			}
        	// 七牛云
			$upManager = new UploadManager();
			$auth = new Auth($qiniu_config['access_key'], $qiniu_config['secret_key']);
			$token = $auth->uploadToken($qiniu_config['bucket']);
			$ext = pathinfo($file->getInfo('name'), PATHINFO_EXTENSION);
			$file_name = time() . '.' . $ext;
			$result = $upManager->putFile($token, $file_name, $file->getRealPath());
			list($ret, $error) = $result;
			if ($error) {
				return array('code' => self::UPLOAD_FAIL, 'errmsg' => '七牛云上传失败', 'savename' =>"", 'filename' => "");
			}
			return array(
				'code' => self::UPLOAD_SUCCESS,
				'errmsg' => '',
				'savename' => $qiniu_config['domain'] . $file_name,	// 七牛云链接
				'filename' => $file_name,
				'type' => 1
			);
		} else {
        	// 普通上传
			$info = $file->rule('uniqid')->validate(['size' => $size, 'ext'=> $ext])->move(ROOT_PATH . 'public/' . $path);
		}

        // 成功上传后 获取上传信息
        if($info){
            // 成功上传后 获取上传信息
            return array('code' => self::UPLOAD_SUCCESS, 'errmsg' => '', 'savename' => $info->getSaveName(), 'filename' => $info->getFilename());
        }else{
            // 上传失败获取错误信息
            return array('code' => self::UPLOAD_FAIL, 'errmsg' => $file->getError(), 'savename' =>"", 'filename' => "");
        }
    }

    // 通用分页
    static function page($page = NULL, $limit = NULL)
    {
        empty($page) && $page = I('page', 1, 'intval');
        empty($limit) && $limit = I('limit', 10, 'intval');
        $offset = ($page - 1) * $limit;
        $offset = max(0, $offset);
        $limit = max(0, $limit);
        return "$offset,$limit";
    }


    // 设置数组下标
    static function setIndex($array, $key_name = 'id')
    {
        $new_array = [];
        foreach ($array as $key => $value) {
            $new_array[$value[$key_name]] = $value;
        }
        return $new_array;
    }

    public static function sortAsTree($data, $pk = 'id', $pid = 'parent_id', $child = 'children', $root = 0)
    {
        // 创建Tree
        $tree = [];
        if (!is_array($data)) {
            return [];
        }

        //创建基于主键的数组引用
        $refer = [];
        foreach ($data as $key => $value_data) {
            $refer[$value_data[$pk]] = &$data[$key];
        }
        foreach ($data as $key => $value_data) {
            //判断是否存在parent
            $parentId = $value_data[$pid];
            if ($root == $parentId) {
                if (!isset($data[$key][$child])) {
                    $data[$key][$child] = [];
                }
                $tree[] = &$data[$key];
            } else {
                if (isset($refer[$parentId])) {
                    $parent = &$refer[$parentId];
                    if (!isset($parent[$child])) {
                        $parent[$child] = [];
                    }
                    $parent[$child][] = &$data[$key];
                }
            }
        }

        return $tree;
    }

    /**
     * 将list2的某个值，根据list1的某个键，对应插入list1
     * @param $list1
     * @param $list2
     * @param string $key
     * @param string $obj_key
     */
    public static function join(&$list1, $list2, string $key, string $obj_key)
    {
        $map = self::setIndex($list2);
        foreach ($list1 as $item) {
            $obj = $map[$item[$key]] ?? [];
            if (empty($obj)) {
                $item[$obj_key] = [];
            }
            else {
                $item[$obj_key] = $obj;
            }
        }
    }

    /**
     * 将list2的多个值，根据list1的某个键，对应插入list1
     * @param $list1
     * @param string $key1
     * @param $list2
     * @param string $key2
     * @param string $target_key
     */
    public static function joinArr(&$list1, string $key1, $list2, string $key2, string $target_key)
    {
        $map = [];
        foreach ($list2 as $item) {
            $key = $item[$key2] ?? '';
            if ($key === '') {
                continue;
            }

            if (!isset($map[$key])) {
                $map[$key] = [];
            }
            $map[$key][] = $item;
        }

        foreach ($list1 as $item) {
            $key = $item[$key1] ?? '';
            if ($key === '') {
                continue;
            }
            $item[$target_key] = $map[$key] ?? [];
        }
    }

}