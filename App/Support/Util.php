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
namespace App\Support;
class Util
{
    public static $iphand;
    /**
     * 获得用户的真实IP 地址
     *
     * HTTP_X_FORWARDED_FOR 的信息可以进行伪造
     * 对于需要检测用户IP是否重复的情况，如投票程序，为了防止IP伪造
     * 可以使用 REMOTE_ADDR + HTTP_X_FORWARDED_FOR 联合使用进行杜绝用户模拟任意IP的可能性
     *
     * @param 多个用多行分开
     * @return void
     */
    public static function get_client_ip()
    {
        if(isset(\SilangPHP\SilangPHP::$app->request->header['x-real-ip']))
        {
            return \SilangPHP\SilangPHP::$app->request->header['x-real-ip'];
        }
        //分析代理IP
        if( isset(\SilangPHP\SilangPHP::$app->request->header['x-forwarded-for2']) )
        {
            \SilangPHP\SilangPHP::$app->request->header['x-forwarded-for2'] = \SilangPHP\SilangPHP::$app->request->header['x-forwarded-for2'];
        }
        if( isset(\SilangPHP\SilangPHP::$app->request->header['x-forwarded-for']) )
        {
            $arr = explode(',', \SilangPHP\SilangPHP::$app->request->header['x-forwarded-for']);
            foreach ($arr as $ip)
            {
                $ip = trim($ip);
                if ($ip != 'unknown' ) {
                    $client_ip = $ip; break;
                }
            }
        }
        else
        {
            $client_ip = isset(\SilangPHP\SilangPHP::$app->request->server['remote_addr']) ? \SilangPHP\SilangPHP::$app->request->server['remote_addr'] : '';
        }
        preg_match("/[\d\.]{7,15}/", $client_ip, $onlineip);
        $client_ip = ! empty($onlineip[0]) ? $onlineip[0] : '0.0.0.0';
        return $client_ip;
    }

    /**
     * 是否手机移动端
     */
    public static function isMobile() {
        // 如果有HTTP_X_WAP_PROFILE则一定是移动设备
        if (isset(\SilangPHP\SilangPHP::$app->request->header['x-wap-profile'])) {
          return true;
        }
        // 如果via信息含有wap则一定是移动设备,部分服务商会屏蔽该信息
        if (isset(\SilangPHP\SilangPHP::$app->request->header['via'])) {
          // 找不到为flase,否则为true
          return stristr(\SilangPHP\SilangPHP::$app->request->header['via'], "wap") ? true : false;
        }
        // 脑残法，判断手机发送的客户端标志,兼容性有待提高。其中'MicroMessenger'是电脑微信
        if (isset(\SilangPHP\SilangPHP::$app->request->header['user-agent'])) {
          $clientkeywords = array('nokia','sony','ericsson','mot','samsung','htc','sgh','lg','sharp','sie-','philips','panasonic','alcatel','lenovo','iphone','ipod','blackberry','meizu','android','netfront','symbian','ucweb','windowsce','palm','operamini','operamobi','openwave','nexusone','cldc','midp','wap','mobile','MicroMessenger');
          // 从HTTP_USER_AGENT中查找手机浏览器的关键字
          if (preg_match("/(" . implode('|', $clientkeywords) . ")/i", strtolower(\SilangPHP\SilangPHP::$app->request->header['user-agent']))) {
            return true;
          }
        }
        // 协议法，因为有可能不准确，放到最后判断
        if (isset (\SilangPHP\SilangPHP::$app->request->header['accept'])) {
          // 如果只支持wml并且不支持html那一定是移动设备
          // 如果支持wml和html但是wml在html之前则是移动设备
          if ((strpos(\SilangPHP\SilangPHP::$app->request->header['accept'], 'vnd.wap.wml') !== false) && (strpos(\SilangPHP\SilangPHP::$app->request->header['accept'], 'text/html') === false || (strpos(\SilangPHP\SilangPHP::$app->request->header['accept'], 'vnd.wap.wml') < strpos(\SilangPHP\SilangPHP::$app->request->header['accept'], 'text/html')))) {
            return true;
          }
        }
        return false;
    }

    /**
     * 生成盐
     */
    public static function generateSalt($length = 12,$chars = null){
        if( empty($chars) ){
          $chars = 'abcdefghijklmnopqrstuvwxyz0123456789';
        }
        $chars = str_shuffle($chars);
        $num = $length < strlen($chars) - 1 ? $length:strlen($chars) - 1;
        return substr($chars,0,$num);
    }

    /**
     * 解析ip地址
     */
    public static function getIPCity($ip)
    {
        if(!self::$iphand)
        {
            self::$iphand = new \APP\Support\Ip();
        }
        $data = self::$iphand->find($ip);
        $result['Country'] = $data['0'] ?? '';
        $result['Province'] = $data['1'] ?? '';
        $result['City'] = $data['2'] ?? '';
        return $result;
    }

    /**
     * 判断是否为utf8字符串
     * @parem $str
     * @return bool
     */
    public static function is_utf8($str)
    {
        if ($str === mb_convert_encoding(mb_convert_encoding($str, "UTF-32", "UTF-8"), "UTF-8", "UTF-32"))
        {
            return true;
        }
        else
        {
            return false;
        }
    }

    /**
     * utf8编码模式的中文截取2，单字节截取模式
     * 这里不使用mbstring扩展
     * @return string
     */
    public static function utf8_substr($str, $slen, $startdd=0)
    {
        return mb_substr($str , $startdd , $slen , 'UTF-8');
    }

    /**
     * utf-8中文截取，按字数截取模式
     * @return string
     */
    public static function utf8_substr_num($str, $length, $start=0)
    {
        preg_match_all('/./su', $str, $ar);
        if( count($ar[0]) <= $length ) {
            return $str;
        }
        $tstr = '';
        $n = 1;
        for($i=0; isset($ar[0][$i]); $i++)
        {
            if($n < $length)
            {
                $tstr .= $ar[0][$i];
                /*
                if( strlen($ar[0][$i]) == 1) $n += 0.5;
                else $n++;
                */
                $n++;
            } else {
                break;
            }
        }
        return $tstr;
    }

    /**
     * 转换单位
     * @param $size
     * @return string
     */
    public static function bunit_convert($size)
    {
        $unit=array('b','kb','mb','gb','tb','pb');
        return @round($size/pow(1024,($i=floor(log($size,1024)))),2).' '.$unit[$i];
    }

    /**
     * 查看api接口情况
     * 此情况要开启devlog
     * @param integer $mode [1天|2时|3分|4秒]
     * @param string $nowdate
     * @return void
     */
    public static function apirun($mode = 1, $nowdate = '')
    {
        // 按天模式
        if(empty($nowdate))
        {
            $nowdate = date("Ymd");
        }
        $nowdate = $nowdate.".log";
        $path = PS_RUNTIME_PATH."/apirun/";
        $apiresult = [];
        foreach (new \DirectoryIterator($path) as $fileInfo) {
            if($fileInfo->isDot()) continue;
            if($fileInfo->isDir())
            {
                $spath = $fileInfo->getPathname();
                $module = $fileInfo->getFilename();
                $subPath = new \DirectoryIterator($spath);
                foreach($subPath as $file)
                {
                    if($file->isDot()) continue;
                    if($file->getFilename() == $nowdate)
                    {
                        $handle = @fopen($spath."/".$nowdate, "r");
                        if ($handle) {
                            while (($buffer = fgets($handle, 4096)) !== false) {
                                $barr = explode("|", trim($buffer));
                                switch($mode)
                                {
                                    case 1:
                                        $dkey = date("Ymd", $barr[1]);
                                        break;
                                    case 2:
                                        $dkey = date("YmdH", $barr[1]);
                                        break;
                                    case 3:
                                        $dkey = date("YmdH:i", $barr[1]);
                                        break;
                                    case 4:
                                        $dkey = date("YmdH:i:s", $barr[1]);
                                        break;
                                    default:
                                        $dkey = date("Ymd", $barr[1]);
                                        break;
                                }

                                if(!isset($apiresult[$module][$dkey]))
                                {
                                    $apiresult[$module][$dkey] = 0;
                                }
                                $apiresult[$module][$dkey] += 1;
                            }
                            
                            if (!feof($handle)) {
                                echo "Error: unexpected fgets() fail\n";
                            }
                            fclose($handle);
                        }
                        
                    }
                }
            }
        }

        foreach($apiresult as $module => $mdata)
        {
            ksort($apiresult[$module]);
        }
        echo '查看模式'.$mode.PHP_EOL;
        foreach($apiresult as $module => $mdata)
        {
            echo "[".$module."]".PHP_EOL;
            foreach($mdata as $index => $indexnum)
            {
                echo $index."请求".$indexnum.PHP_EOL;
            }
        }
    }

}