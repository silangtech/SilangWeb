<?php
/*LICENSE
+-----------------------------------------------------------------------+
| SilangPHP Framework                                                   |
+-----------------------------------------------------------------------+
| This program is free software; you can redistribute it and/or modify  |
| it under the terms of the GNU General Public License as published by  |
| the Free Software Foundation. You should have received a copy of the  |
| GNU General Public License along with this program.  If not, see      |
| http://www.gnu.org/licenses/.                                         |
| Copyright (C) 2020. All Rights Reserved.                              |
+-----------------------------------------------------------------------+
| Supports: http://www.github.com/silangtech/SilangPHP                  |
+-----------------------------------------------------------------------+
*/
declare(strict_types=1);
namespace App\Support;

class Tpl
{
    //数据集合
    public static $tpl_result = ['phpshow'=>'SilangPHP'];

    /**
     * 模板文件
     * @param $file_name
     * @return string
     */
    public static function tpl_file($file_name)
    {
        $file =  self::include_file($file_name);
        return $file;
    }

    /**
     * 当前Action的赋值
     * 数据赋值
     * @param $key
     * @param $value
     */
    public static function assign($key,$value)
    {
        self::$tpl_result[$key] = $value;
    }

    /**
     * 加载所需文件
     */
    public static function include_file($file_name)
    {
        return PS_APP_PATH.'/View/'.$file_name.".php";
    }

    /**
     * 显示模板
     * @param $file_name
     */
    public static function display($file_name = '')
    {
        \extract(self::$tpl_result);
        \ob_start();
        try {
            include self::include_file($file_name);
            // ob_flush();
        } catch (\Throwable $e) {
            echo $e;
        }
        self::$tpl_result = ['phpshow'=>'SilangPHP'];
        return \ob_get_clean();

    }
}