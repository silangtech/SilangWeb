<?php
namespace App\Library;
use App\Model\GameChannelModel;
use SilangPHP\Facade\Log;
use SilangPHP\SilangPHP;

class Util {

    const LOG_REG = 'userReg'; //用户注册
    const LOG_PAY_REQUEST = 'payRequest'; //下单
    const LOG_CALLBACK = 'payCallback'; //下单
    const LOG_NOTIFY_CP = 'notifyCp'; //发货

    /**
     * 验签keyConf
     */
    public static function checkSign($postData = '',$key='appkey')
    {
        // 都要sign的情况下，直接__construct进行就好，接口限定post
        if(empty($postData))
        {
            $postData1 = $postData = SilangPHP::$app->request->posts;
        }else{
            $postData1 = $postData;
        }
        if(empty($postData['sign']))
        {
            Log::error("sign1".json_encode($postData1));
            return SilangPHP::$app->response->json(-1,'sign不通过！！');
        }
        $sign = $postData['sign'];
        unset($postData['sign']);
        ksort($postData);
        // @todo 根据channel拿appKey
        // 根据channelid获取相关配置
        $appkey = '';
        if(isset($postData['channelid']))
        {
            $secret = GameChannelModel::getInstance()->getSecret($postData['channelid']);
            if($secret)
            {
                $appkey = $secret[$key];
            }
        }else{
            // 隐藏密钥，不用了
            // $appkey = 'Pwt4eMDwXPQLLUo9vEMDAxPK';
        }
        if(empty($appkey))
        {
            Log::error("sign2".json_encode($postData1));
            return SilangPHP::$app->response->json(-1,'sign不通过1');
        }
        $str = implode("",$postData).$appkey;
//        echo $str;exit;
        $verifySign = md5($str);
//        echo $verifySign;exit;
        if($sign != $verifySign)
        {
            Log::error('sign_error:'.json_encode($postData1));
            return SilangPHP::$app->response->json(-1,'sign不通过');
        }
        return true;
    }

    /**
     * 日期范围
     *
     * @param [type] $starttime
     * @param [type] $endtime
     * @return void
     */
    public static function daterange($starttime,$endtime)
    {
        $datearr = [];
        while($starttime<=$endtime)
        {
            $date = date("Y-m-d",strtotime($starttime));
            $starttime = date("Ymd",strtotime($starttime)+86400);
            $datearr[] = $date;
        }
        return $datearr;
    }

    /**
     * 相隔天数
     */
    public static function datedistance($starttime,$endtime)
    {
        date_default_timezone_set("Asia/Shanghai");
        $one = strtotime($starttime);//开始时间 时间戳
        $tow = strtotime($endtime);//结束时间 时间戳
        $cle = $tow - $one; //得出时间戳差值
        $day = floor($cle/3600/24) + 1;
        return $day;
    }


    /**
     * bnlog也是调用Log::record
     */
    public static function bnlog($errCode = '00', $logType = '', $gameId = '', $errorReason = '', $detailLogInfo = '') {

        $infoKey = implode('-', [
            $logType,
            $gameId ? $gameId : ''
        ]);

        $msg = '[out-api] '
            . $infoKey . " "
            . ($errCode ? "{$errCode} " : "--")
            . ($errorReason ? "{$errorReason} " : "--")
            . ($detailLogInfo ? "{$detailLogInfo} " : "--");
        Log::record($msg);
    }

    /**
     * 获取请求客户端ip
     */
    public static function getClientIp() {
        static $ip = null;
        if ($ip !== null) {
            return $ip;
        }
        if (isset($_SERVER["HTTP_X_FORWARDED_FOR"])) {
            $arr = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            foreach ($arr as $_ip) {
                $_ip = trim($_ip);
                if ($_ip !== 'unknown') {
                    $ip = $_ip;
                    break;
                }
            }
        } else if (isset($_SERVER["HTTP_CLIENT_IP"])) {
            $ip = $_SERVER["HTTP_CLIENT_IP"];
        } else {
            $ip = $_SERVER["REMOTE_ADDR"];
        }
        if ($ip === null) {
            $ip = '';
        }
        return $ip;
    }

    /**
     * @param $url
     * @param int $timeout
     * @param null $postfields
     * @param array $extraOptions
     * @param int $retries 重试次数
     * @return string
     * @throws \Exception
     */
    public static function curlRequest($url,
                                       $timeout = 5,
                                       $postfields = null,
                                       array $extraOptions = [],
                                       $retries = 1) {
        static $defaultHeaders = [
            'Connection: close',
            'Cache-Control: no-cache'
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $headers = $defaultHeaders;
        if (isset($extraOptions[CURLOPT_HTTPHEADER])) {
            foreach ($extraOptions[CURLOPT_HTTPHEADER] as $v) {
                $headers[] = $v;
            }
        }
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        if (strpos($url, 'https://') !== false) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        }
        if ($timeout > 0) {
            curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        }
        if ($postfields !== null) {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postfields);
        }
        if (!empty($extraOptions)) {
            curl_setopt_array($ch, $extraOptions);
        }

        $result = false;
        while (($result === false) && (--$retries >= 0)) {
            $result = curl_exec($ch);
        }

        if ($result === false) {
            $error = curl_error($ch);
            $errno = curl_errno($ch);
            curl_close($ch);
            throw new \Exception("curlerror, {$error}", $errno);
        }
        curl_close($ch);
        return $result;
    }
    /**
     * 判断是否苹果公司ip
     * 增加美国ip
     * @param $ip
     * @return bool
     */
    public static function isAppleIp($ip) {

        $iplong = ip2long($ip);
        if ($iplong !== false) {
            //这里加上ip库美国判断
            $datx2 = new IP();
            $result = $datx2->find($ip);
            if(!empty($result))
            {
                if(isset($result['0']))
                {
                    if($result['0'] == '美国')
                    {
                        return true;
                    }
                }
            }

            $iplong = sprintf('%u', $iplong);
            if (
                ($iplong >= 285212672 && $iplong <= 301989887) ||   //17.0.0.0 ~ 17.255.255.255
                ($iplong >= 1063051264 && $iplong <= 1063059455) || //63.92.224.0 ~ 63.92.255.255
                ($iplong >= 1103566336 && $iplong <= 1103566847) || //65.199.22.0 ~ 65.199.23.255
                ($iplong >= 3222030848 && $iplong <= 3222031103) || //192.12.74.0 ~ 192.12.74.255
                ($iplong >= 3224041728 && $iplong <= 3224041983) || //192.42.249.0 ~ 192.42.249.255
                ($iplong >= 3427778048 && $iplong <= 3427778303) || //204.79.190.0 ~ 204.79.190.255
                ($iplong >= 2427584512 && $iplong <= 2427600895)    //144.178.0.0 ~ 144.178.63.255
            ) {
                return true;
            }
        }
        return false;
    }

    /**
     * 是否手机移动端
     */
    public static function isMobile() {
        // 如果有HTTP_X_WAP_PROFILE则一定是移动设备
        if (isset($_SERVER['HTTP_X_WAP_PROFILE'])) {
          return true;
        }
        // 如果via信息含有wap则一定是移动设备,部分服务商会屏蔽该信息
        if (isset($_SERVER['HTTP_VIA'])) {
          // 找不到为flase,否则为true
          return stristr($_SERVER['HTTP_VIA'], "wap") ? true : false;
        }
        // 脑残法，判断手机发送的客户端标志,兼容性有待提高。其中'MicroMessenger'是电脑微信
        if (isset($_SERVER['HTTP_USER_AGENT'])) {
          $clientkeywords = array('nokia','sony','ericsson','mot','samsung','htc','sgh','lg','sharp','sie-','philips','panasonic','alcatel','lenovo','iphone','ipod','blackberry','meizu','android','netfront','symbian','ucweb','windowsce','palm','operamini','operamobi','openwave','nexusone','cldc','midp','wap','mobile','MicroMessenger');
          // 从HTTP_USER_AGENT中查找手机浏览器的关键字
          if (preg_match("/(" . implode('|', $clientkeywords) . ")/i", strtolower($_SERVER['HTTP_USER_AGENT']))) {
            return true;
          }
        }
        // 协议法，因为有可能不准确，放到最后判断
        if (isset ($_SERVER['HTTP_ACCEPT'])) {
          // 如果只支持wml并且不支持html那一定是移动设备
          // 如果支持wml和html但是wml在html之前则是移动设备
          if ((strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') !== false) && (strpos($_SERVER['HTTP_ACCEPT'], 'text/html') === false || (strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') < strpos($_SERVER['HTTP_ACCEPT'], 'text/html')))) {
            return true;
          }
        }
        return false;
    }

    // 整合获取系统相关信息
    public static function getOsInfo($device_info) {
        $os = $device_info['os'] ? $device_info['os'] : '';
        if ($device_info['os'] == '') {
            $os = $device_info['android_version'] ? 'Android' : 'ios';
        }

        $os_info = array(
            // 操作系统
            'os' => $os,
            // 操作系统版本
            'os_version' => $device_info['android_version'] ?
                $device_info['android_version'] :
                $device_info['system_version'],
            // sdk版本
            'sdk_version' => $device_info['sdk_version'] ?
                $device_info['sdk_version'] :
                '',
            // 设备名称
            'device_name' => $device_info['device_name'] ?
                $device_info['device_name'] :
                $device_info['system_name']
        );

        if ($os == 'Android') {
            $os_info['ad_id'] = $device_info['ad_id'] ? $device_info['ad_id'] : '';
            $os_info['and_id'] = $device_info['and_id'] ? $device_info['and_id'] : '';
        }

        return json_encode($os_info);
    }

    public static function isPhone($uname) {
        static $pattern = '/^1\d{10}$/';
        return preg_match($pattern, $uname) === 1;
    }

    //检查账号是否是4-16位字母或数字
    public static function isLegalUsername($uname) {
        $pattern = '/^[\w+]{6,14}$/';
        return preg_match($pattern, $uname);
    }

    public static function urlDecode($data) {
        if (is_array($data)) {
            $ret = [];
            foreach ($data as $k => $v) {
                $ret[$k] = urldecode($v);
            }
            return $ret;
        } else {
            return urldecode($data);
        }
    }

    /**
     * Post提交JSON数据
     * @param string $url
     * @param string JSON格式 {"a":"A","b":"B"}
     * @param int $timeout 请求超时（秒）
     * @return string
     */
    public static function curlPostJson($url, $fields = '', $timeout = 3)
    {
        $ch = curl_init();
        if (strpos($url, 'https') === 0) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        }
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json; charset=utf-8']);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        $data = curl_exec($ch);
        curl_close($ch);
        return $data;
    }

    /**
     * 获取当前访问的完整url
     * @return string
     */
    public static function GetCurUrl() {
        $url = 'http://';
        if(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') {
            $url = 'https://';
        }

        // 判断端口
        if($_SERVER['SERVER_PORT'] != '80') {
            $url .= $_SERVER['SERVER_NAME'] . ':' . $_SERVER['SERVER_PORT'] . $_SERVER['REQUEST_URI'];
        } else {
            $url .= $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];
        }

        return $url;
    }

    /**
     * PHP判断当前协议是否为HTTPS
     */
    public static function is_https() {
        if ( !empty($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off') {
            return true;
        } elseif ( isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https' ) {
            return true;
        } elseif ( !empty($_SERVER['HTTP_FRONT_END_HTTPS']) && strtolower($_SERVER['HTTP_FRONT_END_HTTPS']) !== 'off') {
            return true;
        }
        return false;
    }

    /**
     * 将一个字符串部分字符用$re替代隐藏
     * @param string    $string   待处理的字符串
     * @param int       $start    规定在字符串的何处开始，
     *                            正数 - 在字符串的指定位置开始
     *                            负数 - 在从字符串结尾的指定位置开始
     *                            0 - 在字符串中的第一个字符处开始
     * @param int       $length   可选。规定要隐藏的字符串长度。默认是直到字符串的结尾。
     *                            正数 - 从 start 参数所在的位置隐藏
     *                            负数 - 从字符串末端隐藏
     * @param string    $re       替代符
     * @return string   处理后的字符串
     */
    public static function hidestr($string, $start = 0, $length = 0, $re = '*') {
        if (empty($string)) return false;
        $strarr = array();
        $mb_strlen = mb_strlen($string);
        while ($mb_strlen) {//循环把字符串变为数组
            $strarr[] = mb_substr($string, 0, 1, 'utf8');
            $string = mb_substr($string, 1, $mb_strlen, 'utf8');
            $mb_strlen = mb_strlen($string);
        }
        $strlen = count($strarr);
        $begin  = $start >= 0 ? $start : ($strlen - abs($start));
        $end    = $last   = $strlen - 1;
        if ($length > 0) {
            $end  = $begin + $length - 1;
        } elseif ($length < 0) {
            $end -= abs($length);
        }
        for ($i=$begin; $i<=$end; $i++) {
            $strarr[$i] = $re;
        }
//        if ($begin >= $end || $begin >= $last || $end > $last) return false;
        return implode('', $strarr);
    }

    /**
     * 获取设备类型
     */
    public static function getDevice()
    {
        $os = 0;
        $HTTP_USER_AGENT = $_SERVER['HTTP_USER_AGENT'];
        if (strstr($HTTP_USER_AGENT, 'Android')) {
            $os = 1;
        } else if (strstr($HTTP_USER_AGENT, 'iPhone')
            || strstr($_SERVER['HTTP_USER_AGENT'], 'iPad')) {
            $os = 2;
        }else if (strstr($HTTP_USER_AGENT, 'Windows')) {
            $os = 3;
        }

        return $os;
    }

    /**
     * 替换微信名中的表情符号
     * @param $text 包含变轻符号的字符串
     * @param string $replaceTo 要将表情符号替换成自己设置的 字符
     * @return string|string[]|null
     */
    public static function filterEmoji($text, $replaceTo = 'x'){
        $clean_text = "";
        // Match Emoticons
        $regexEmoticons = '/[\x{1F600}-\x{1F64F}]/u';
        $clean_text = preg_replace($regexEmoticons, $replaceTo, $text);
        // Match Miscellaneous Symbols and Pictographs
        $regexSymbols = '/[\x{1F300}-\x{1F5FF}]/u';
        $clean_text = preg_replace($regexSymbols, $replaceTo, $clean_text);
        // Match Transport And Map Symbols
        $regexTransport = '/[\x{1F680}-\x{1F6FF}]/u';
        $clean_text = preg_replace($regexTransport, $replaceTo, $clean_text);
        // Match Miscellaneous Symbols
        $regexMisc = '/[\x{2600}-\x{26FF}]/u';
        $clean_text = preg_replace($regexMisc, $replaceTo, $clean_text);
        // Match Dingbats
        $regexDingbats = '/[\x{2700}-\x{27BF}]/u';
        $clean_text = preg_replace($regexDingbats, $replaceTo, $clean_text);
        return $clean_text;
    }
}
