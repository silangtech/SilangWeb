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

use Exception;
use SilangPHP\SilangPHP;

/**
 * 最简单的mysql db类
 */
class Mysql
{
    //数据库链接
    public $link;
    //慢查询时间
    private $late_time = 3;
    public function __construct($conn = 'master')
    {
        $this->connect($conn);
    }
    
    /**
     * 连接数据库
     */
    public function connect($conn = '')
    {
        if(empty($conn))
        {
            $conn = "master";
        }
        $config = \SilangPHP\Config::get("db.mysql")[$conn];
        if(empty($config['charset']))
        {
            $charset = 'utf8';
        }else{
            $charset = $config['charset'];
        }
        $dsn    = 'mysql:dbname=' . $config['dbname'] . ';host=' . $config["host"] . ';port=' . $config['port'];
        $this->pdo = new \PDO($dsn, $config['username'], $config["password"],
            array(
                \PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES ' . $charset
            ));
        $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        $this->pdo->setAttribute(\PDO::ATTR_STRINGIFY_FETCHES, false);
        $this->pdo->setAttribute(\PDO::ATTR_EMULATE_PREPARES, false);
    }

    /**
     * 执行mysql处理
     * @param $sql
     */
    public function query(string $sql = '')
    {
        $result = '';
        $starttime = microtime(true);
        $result = false;
        try{
            $result = $this->pdo->query($sql);
        }catch(Exception $e)
        {
            $message = $e->getMessage();
        }
        $endtime = microtime(true);
        $lasttime = $endtime - $starttime;
        if (!$result) {
            //调试模式才能显示 查看语句的时效
            if(SilangPHP::$debug == 1)
            {
                echo $sql.PHP_EOL;
                echo "sql_time:".$lasttime.PHP_EOL;
            }
        }
        return $result;
    }

    /**
     * 获取单个数据
     */
    public function get_one(string $sql = '')
    {
        $row = false;
        if(!strpos($sql,'limit'))
        {
            //$sql语句结尾不能有;号
            $sql = $sql." limit 1 ";
        }
        $result = $this->query($sql);
        if($result)
        {
            $row = $result->fetch(\PDO::FETCH_ASSOC);
        }
        return $row;
    }

    /**
     * 获取所有数据
     */
    public function get_all(string $sql = '')
    {
	$row = false;
        $result = $this->query($sql);
        if($result)
        {
            $row = $result->fetchAll(\PDO::FETCH_ASSOC);
        }
        return $row;
    }

    /**
     * 处理大数组
     * @param $sql
     * @return \Generator
     */
    public function get_big_all(string $sql = '')
    {
        $result = $this->query($sql);
        while($row=$result->fetch($result,\PDO::FETCH_ASSOC)) {
            yield $row;
        }
    }

    /**
     * 获取插入的id
     * @return int|string
     */
    public function insert_id()
    {
        return $this->pdo->lastInsertId();
    }

}