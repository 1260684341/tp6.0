<?php
namespace app\common\kit;

use PHPExcel_Reader_CSV;
use PHPExcel_Reader_Excel2007;
use PHPExcel_Reader_Excel5;
use PHPExcel_Style_Color;
use SplFileObject;
use think\Exception;
use PHPExcel;
use PHPExcel_Writer_Excel2007;
use PHPExcel_IOFactory;
use PHPExcel_Worksheet_Drawing;

class ExcelKit
{
    public static function excelReader($file)
    {
        /** PHPExcel_IOFactory */
        require_once  APP_PATH . '/common/PHPExcel/Classes/PHPExcel/IOFactory.php';
        $extension = strtolower( pathinfo($file, PATHINFO_EXTENSION) );

        try {
            if ($extension =='xlsx') {
                $objReader = new PHPExcel_Reader_Excel2007();
                $objExcel = $objReader->load($file);
            } else if ($extension =='xls') {
                $objReader = new PHPExcel_Reader_Excel5();
                $objExcel = $objReader->load($file);
            } else if ($extension=='csv') {
                $PHPReader = new PHPExcel_Reader_CSV();
                //默认输入字符集
                //$PHPReader->setInputEncoding('GBK');
                //默认的分隔符
                $PHPReader->setDelimiter(',');
                //载入文件
                $objExcel = $PHPReader->load($file);
            }

            $sheet = $objExcel->getSheet(0);
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
        }
        catch (\Exception $e) {
            return [];
        }
    }


    public static function excelRowReader($file, $offset, $len) {

        $splFileObject = new SplFileObject($file, 'rb');
        $splFileObject->seek(filesize($file));
        $splFileObject->seek(($offset - 1) * $len);
        $content = null;
        while ((!($splFileObject->eof()) && $len)){
            $rowData = $splFileObject->current();
            $rowData = explode(',', $rowData);
            foreach ($rowData as $key => $col) {
                $rowData[$key] = trim($col);
            }
            $content[] = $rowData;
            $splFileObject->next();
            $len--;
        }
        return $content;
    }

    /*
    *处理Excel导出
    *@param $datas array 设置表格数据
    *@param $titlename string 设置head
    *@param $title string 设置表头
    */
    public static function export($datas,$titlename,$title,$filename){
        $str = "<html xmlns:o=\"urn:schemas-microsoft-com:office:office\"\r\nxmlns:x=\"urn:schemas-microsoft-com:office:excel\"\r\nxmlns=\"http://www.w3.org/TR/REC-html40\">\r\n<head>\r\n<meta http-equiv=Content-Type content=\"text/html; charset=utf-8\">\r\n</head>\r\n<body>";
        $str .="<table border=1><thead>".$titlename."</thead>";
        $str .= $title;
        foreach ($datas as $key=> $rt) {
            $str .= "<tr>";
            foreach ( $rt as $k => $v ) {
                $str .= "<td style=\"vnd.ms-excel.numberformat:@\">{$v}</td>";
            }
            $str .= "</tr>\n";
        }

        $str .= "</table></body></html>";
        header( "Content-Type: application/vnd.ms-excel; name='excel'" );
        header( "Content-type: application/octet-stream" );
        header( "Content-Disposition: attachment; filename=".$filename );
        header( "Cache-Control: must-revalidate, post-check=0, pre-check=0" );
        header( "Pragma: no-cache" );
        header( "Expires: 0" );
        exit($str);
    }

    public static function write($title = [], $data = [], $file_name = '', $save_path = './', $width = []){
        // 引入类
        require_once  base_path() . '/common/PHPExcel/Classes/PHPExcel/IOFactory.php';
        $php_excel = new PHPExcel();

        // 横向单元格标识
        $cell_name = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', 'AA', 'AB', 'AC', 'AD', 'AE', 'AF', 'AG', 'AH', 'AI', 'AJ', 'AK', 'AL', 'AM', 'AN', 'AO', 'AP', 'AQ', 'AR', 'AS', 'AT', 'AU', 'AV', 'AW', 'AX', 'AY', 'AZ');

        // 设置sheet名称
        $php_excel->getActiveSheet(0)->setTitle('商品数据');

        // 循环设置所有单元格都为自动列宽
        foreach ($width as $row_cell) {
            // $php_excel->getActiveSheet(0)->getColumnDimension($row_cell)->setAutoSize(true);
            $php_excel->getActiveSheet(0)->getColumnDimension($row_cell['column'])->setWidth($row_cell['size']);
        }

        // 设置纵向单元格标识
        $row = 1;
        if ($title) {
            $i = 0;
            // 设置列标题
            foreach($title AS $v){
                $php_excel->setActiveSheetIndex(0)->setCellValue($cell_name[$i] . $row, $v);
                $i++;
            }
            $row++;
        }

        //填写数据
        if ($data) {
            $i = 0;
            foreach($data AS $value){
                $j = 0;
                foreach($value AS $cell){
                    if (mb_substr($cell, 0, 3) == 'img') {
                        $img_src = mb_substr($cell, 3);

                        if (!file_exists($img_src)) {
                            $php_excel->getActiveSheet(0)->setCellValue($cell_name[$j] . ($i + $row), '该图片不存在');
                        } else {
                            // 实例化插入图片类
                            $obj_drawing = new PHPExcel_Worksheet_Drawing();
                            // 设置图片路径
                            $obj_drawing->setPath($img_src);
                            $obj_drawing->setWidth(100);
                            // 设置图片高度
                            $obj_drawing->setHeight(100);
                            // 设置图片要插入的单元格
                            $obj_drawing->setCoordinates($cell_name[$j] . ($i + $row));
                            // 设置图片所在单元格的格式
                            // $objDrawing->setOffsetX(80);
                            $obj_drawing->getShadow()->setVisible(true);
                            $obj_drawing->getShadow()->setDirection(50);
                            $obj_drawing->setWorksheet($php_excel->getActiveSheet());

                            $php_excel->getActiveSheet()->getRowDimension(($i + $row))->setRowHeight(100);
                        }

                    } else {
                        $php_excel->getActiveSheet(0)->setCellValue($cell_name[$j] . ($i + $row), $cell);
                    }
                    $j++;
                }
                $i++;
            }
        }
        //文件名处理
        if (!$file_name) {
            $file_name = uniqid(time(),true);
        }

        $obj_write = PHPExcel_IOFactory::createWriter($php_excel, 'Excel2007');

        // $_fileName = iconv("utf-8", "gb2312", $fileName);   //转码

        $save_path = $save_path . $file_name . '.xlsx';

        $obj_write->save($save_path);

        return $save_path . $file_name.'.xlsx';
    }

    /**
     * 导出excel
     * @param array $data 导入数据
     * @param string $savefile 导出excel文件名
     * @param array $fileheader excel的表头
     * @param string $sheetname sheet的标题名
     */
    public static function excel_writer($data, $savefile = "", $fileheader = [], $sheetname)
    {
        require_once app_path() . '/common/PHPExcel/Classes/PHPExcel.php';

        $excel = new \PHPExcel();
        if (empty($savefile)) {
            $savefile = time();
        }

        iconv('UTF-8', 'GB2312', $savefile);

        //设置excel属性
        $objActSheet = $excel->getActiveSheet();
        //根据有生成的excel多少列，$letter长度要大于等于这个值
        $letter = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'S', 'Y', 'Z', 'AA', 'AB', 'AC', 'AD', 'AE', 'AF', 'AG', 'AH');
        //设置当前的sheet
        $excel->setActiveSheetIndex(0);
        //设置sheet的name
        $objActSheet->setTitle($sheetname);
        //设置表头
        $fontsize = 10;
        for ($i = 0; $i < count($fileheader); $i++) {

            // 设置表头宽度
            $objActSheet->getColumnDimension($letter[$i])->setWidth(5 * mb_strlen($fileheader[$i]));


            //单元宽度自适应,1.8.1版本phpexcel中文支持勉强可以，自适应后单独设置宽度无效
            //$objActSheet->getColumnDimension("$letter[$i]")->setAutoSize(true);
            //设置表头值，这里的setCellValue第二个参数不能使用iconv，否则excel中显示false

            $objActSheet->setCellValue("$letter[$i]1", $fileheader[$i]);
            //设置表头字体样式
            $objActSheet->getStyle("$letter[$i]1")->getFont()->setName('微软雅黑');
            //设置表头字体大小
            $objActSheet->getStyle("$letter[$i]1")->getFont()->setSize($fontsize);
            //设置表头字体是否加粗
            $objActSheet->getStyle("$letter[$i]1")->getFont()->setBold(true);

            $objActSheet->getStyle("$letter[$i]1")->getFont()->setColor(
                (new PHPExcel_Style_Color(PHPExcel_Style_Color::COLOR_BLUE)));
            //设置表头文字垂直居中
            $objActSheet->getStyle("$letter[$i]1")->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            //设置文字上下居中
            $objActSheet->getStyle($letter[$i])->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
            //设置表头外的文字垂直居中
            $excel->setActiveSheetIndex(0)->getStyle($letter[$i])->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        }

        //这里$i初始值设置为2，$j初始值设置为0，自己体会原因
        for ($i = 2; $i <= count($data) + 1; $i++) {
            $j = 0;
            foreach ($data[$i - 2] as $key => $value) {
                if (is_string($value)) {
                    $objActSheet->setCellValue("$letter[$j]$i", $value);
                } else if (is_array($value)) {

                    if (!key_exists("type", $value)) {
                        continue;
                    } else {
                        if ($value['type'] == "img") {
                            $value = $value['value'];
                            if ($value != '') {
                                $value = iconv("UTF-8", "GB2312", $value); //防止中文命名的文件
                                // 图片生成
                                $objDrawing[$key] = new \PHPExcel_Worksheet_Drawing();
                                // 图片地址
                                $image_file = './static/images/qrcode/' . $value;
                                $objDrawing[$key]->setPath($image_file);
                                // 设置图片宽度高度
                                $objDrawing[$key]->setHeight('100'); //照片高度
                                $objDrawing[$key]->setWidth('100'); //照片宽度
                                // 设置图片要插入的单元格
                                $objDrawing[$key]->setCoordinates('B' . $i);
                                // 图片偏移距离
                                $objDrawing[$key]->setOffsetX(12);
                                $objDrawing[$key]->setOffsetY(12);
                                $objDrawing[$key]->setWorksheet($objActSheet);
                            }
                        }
                    }

                } else {
                    $objActSheet->setCellValue("$letter[$j]$i", $value);
                }

                /*  //不是图片时将数据加入到excel，这里数据库存的图片字段是img
                  if($key != 'img'){
                  }

                  //是图片是加入图片到excel
                  if($key == 'img'){
                     if($value != ''){
                         $value = iconv("UTF-8","GB2312",$value); //防止中文命名的文件
                         // 图片生成
                         $objDrawing[$key] = new \PHPExcel_Worksheet_Drawing();
                         // 图片地址
                         $objDrawing[$key]->setPath('.\Uploads'.$value);
                         // 设置图片宽度高度
                         $objDrawing[$key]->setHeight('80px'); //照片高度
                         $objDrawing[$key]->setWidth('80px'); //照片宽度
                         // 设置图片要插入的单元格
                         $objDrawing[$key]->setCoordinates('D'.$i);
                         // 图片偏移距离
                         $objDrawing[$key]->setOffsetX(12);
                         $objDrawing[$key]->setOffsetY(12);
                         $objDrawing[$key]->setWorksheet($objActSheet);
                     }
                  }*/
                $j++;
                // 删除图片
            }
            //设置单元格高度，暂时没有找到统一设置高度方法
            $objActSheet->getRowDimension($i)->setRowHeight(2 * $fontsize); // 两倍字体的高度
        }
        header('Content-Type: application/vnd.ms-excel');
        //下载的excel文件名称，为Excel5，后缀为xls，不过影响似乎不大
        header('Content-Disposition: attachment;filename="' . $savefile . '.xlsx"');
        header('Cache-Control: max-age=0');
        // 用户下载excel
        require_once APP_PATH . '/common/PHPExcel/Classes/PHPExcel/IOFactory.php';
        $objWriter = PHPExcel_IOFactory::createWriter($excel, 'Excel2007');
        $objWriter->save('php://output');
        //保存excel在服务器上
        //$objWriter = new PHPExcel_Writer_Excel2007($excel);
        //或者$objWriter = new PHPExcel_Writer_Excel5($excel);
        //$objWriter->save("保存的文件地址/".$savefile);
    }


}