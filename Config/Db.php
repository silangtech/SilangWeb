<?php
/**
 * 数据库配置文件
 * Author:shengsheng
 */
return [
    'mysql' => [
        //相当于default
        'default' => [
            'host' => getenv('mysql_host', '172.17.0.4'),
            'port' => getenv('port', '3306'),
            'dbname' => getenv('dbname', 'phpshow'),
            'username' => getenv('mysql_username', 'root'),
            'password' => getenv('mysql_password', 'root'),
        ],
    ]
];