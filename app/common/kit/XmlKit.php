<?php
namespace app\common\kit;

use PHPExcel_Reader_CSV;
use PHPExcel_Reader_Excel2007;
use PHPExcel_Reader_Excel5;
use SplFileObject;

class XmlKit
{

    /**
     * XML编码
     * @param mixed $data 数据
     * @param string $root 根节点名
     * @return string
     */
    static public function xmlEncode($data, $root='xml') {
        $xml   = "<{$root}>";
        $xml   .= self::dataToXml($data);
        $xml   .= "</{$root}>";
        return $xml;
    }

    /**
     * 数据XML编码
     * @param mixed $data 数据
     * @return string
     */
    public static function dataToXml($data) {
        $xml = '';
        foreach ($data as $key => $val) {
            is_numeric($key) && $key = "item id=\"$key\"";
            $xml    .=  "<$key>";
            $xml    .=  ( is_array($val) || is_object($val)) ? self::dataToXml($val)  : self::xmlSafeStr($val);
            list($key, ) = explode(' ', $key);
            $xml    .=  "</$key>";
        }
        return $xml;
    }

    public static function xmlSafeStr($str){
        return '<![CDATA['.preg_replace("/[\\x00-\\x08\\x0b-\\x0c\\x0e-\\x1f]/",'',$str).']]>';
    }

    /**
     * XML解码
     */
    static function xmlDecode($xml) {
        $res = (array)simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA);
        return $res;
    }


    public static function excelReader($file)
    {
        /** PHPExcel_IOFactory */
        require_once  app_path() . '/common/PHPExcel/Classes/PHPExcel/IOFactory.php';

        $extension = strtolower( pathinfo($file, PATHINFO_EXTENSION) );
        try {
            if ($extension =='xlsx') {
                $objReader = new PHPExcel_Reader_Excel2007();
                $objExcel = $objReader->load($file);
            } else if ($extension =='xls') {
                $objReader = new PHPExcel_Reader_Excel5();
                $objExcel = $objReader ->load($file);
            } else if ($extension=='csv') {
                $PHPReader = new PHPExcel_Reader_CSV();
                //默认输入字符集
                //$PHPReader->setInputEncoding('GBK');
                //默认的分隔符
                $PHPReader->setDelimiter(',');
                //载入文件
                $objExcel = $PHPReader->load($file);
            }

            $sheet = $objExcel->getSheet(0); // 读取第一個工作表
            $highestRow = $sheet->getHighestRow(); // 取得总行数
            $highestColumm = $sheet->getHighestColumn(); // 取得总列数

            $data = array();
            /** 循环读取每个单元格的数据 */
            for ($row = 1; $row <= $highestRow; $row++){//行数是以第1行开始
                $dataset = array();
                for ($column = 'A'; $column <= $highestColumm; $column++) {
                    $dataset[] = $sheet->getCell($column.$row)->getValue();
                }
                $data[] = $dataset;
            }

            return $data;


        } catch (\Exception $e) {
            return [];
        }
    }

}