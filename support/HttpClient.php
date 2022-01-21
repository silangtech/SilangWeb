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
namespace sapp\support;


class HttpClient
{
    /**
     * get请求
     * file_get_contents也可以考虑
     */
    public static function get($url, $timeout = 3)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 60);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        $response = curl_exec($ch);
        if (curl_error($ch)) {
            $error_msg = curl_error($ch);
            $error_no = curl_errno($ch);
            curl_close($ch);
            throw new \Exception($error_msg, $error_no);
        }
        curl_close($ch);
        return $response;
    }

    /**
     * post请求
     */
    public static function post($url, $postfields, $timeout = 3)
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $headers = [
            'Connection: close',
            'Cache-Control: no-cache'
        ];

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
        $result = curl_exec($ch);
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
     * post json请求
     */
    public static function postjson($url, $fields, $timeout = 3)
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
     * post upload请求
     */
    public static function postupload($url, $fields, $file, $timeout = 3)
    {
        $ch = curl_init();
        if (strpos($url, 'https') === 0) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        }
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: multipart/form-data']);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        if($fields){
            curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
        }
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);

        // $upload_data = array(
        //     'upload_file' => $file,
        // );
        // curl_setopt($ch, CURLOPT_POSTFIELDS, $upload_data);

        $data = curl_exec($ch);
        curl_close($ch);
        return $data;
    }

    /**
     * Post提交JSON数据
     * @param string $url
     * @param string JSON格式 {"a":"A","b":"B"}
     * @param int $timeout 请求超时（秒）
     * @return string
     */
    public static function curlPostJson($url, $fields = '', $header = ['Content-Type: application/json; charset=utf-8'], $timeout = 3)
    {
        $ch = curl_init();
        if (strpos($url, 'https') === 0) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        }
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
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
     * 某些方法，历史版本的备份
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
     * @param $url
     * @param int $timeout
     * @param null $postfields
     * @param array $extraOptions
     * @param int $retries 重试次数
     * @return string
     * @throws \Exception
     */
    public static function curlConfigRequest($url,
                                       $timeout = 5,
                                       $postfields = null,
                                       array $extraOptions = [],
                                       array $fileconfig = [],
                                       $retries = 1
                                       ) {
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

        // fileconfig
        if(!empty($fileconfig)){
            curl_setopt($ch,CURLOPT_SSLCERT, $fileconfig['cert_path']);
            curl_setopt($ch,CURLOPT_SSLKEY, $fileconfig['key_path']);
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


}