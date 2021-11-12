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

// use SilangPHP\Db\Medoo;
use Illuminate\Database\QueryException;
use Illuminate\Database\Eloquent\Model as Eloquent_Model;
use Illuminate\Database\Capsule\Manager as Capsule;
// use Illuminate\Events\Dispatcher;
// use Illuminate\Container\Container;

class Model extends Eloquent_Model
{
    //表格名
    public $table_name = "";
    //每页条数
    public $limit = 20;
    //指定数据库 又名connection
    public $database = '';
    public $connection_name = '';
    //指定数据库名
    public $db_name = '';
    //当前页数
    public $page = 1;
    //主键
    // primaryKey primary_key
    public $primaryKey = 'id';
    //数据库类型，暂时只支持mysql
    public $db_type = 'mysql';
    //查询字段
    public $fields = '*';
    //表格数据
    public $attr;
    public $conn_status = false;
    public $timestamps = false;
    public function __construct(array $attributes = [])
    {
        try{
            //自动效验表格名
            $this->table();
            $this->connection = $this->connection_name = $this->connection ?? $this->database;
            $prikey = $this->primary_key ?? $this->primaryKey;
            $this->setKeyName($prikey);
            parent::__construct($attributes);
            $this->conn_status = true;
        }catch(Exception $e)
        {
            $this->conn_status = false;
            throw new \PDOException($e->getMessage());
        }
    }

    /**
     * 数据库名
     * @return string
     */
    public function table($table_name = ''){
        if(!empty($this->table))
        {
            $this->table_name = $this->table;
        }
        if(!empty($table_name))
        {
            $this->table_name = $table_name;
        }
        if($this->table_name === ""){
            $table_name = get_called_class();
            $table_name = str_replace(["mod_","Model"], "", $table_name);
            $this->table_name = $table_name;
        }
        $this->table = $this->table_name;
        return $this;
    }

    public function recordError($e)
    {
        $sql = $e->getSql();
        $message = $e->getMessage();
        \file_put_contents(PS_RUNTIME_PATH.'/sqlerror.txt',"sql:".$sql."|message:".$message."\r\n",FILE_APPEND|LOCK_EX);
        throw $e;
    }


    /**
     * 获取指定sql一条数据
     */
    public function get_sql_one($sql)
    {
        try{
            $data = Capsule::connection($this->connection_name)->selectOne($sql);
            $data = json_decode(json_encode($data), true);
            return $data;
        }catch (QueryException $e) {
            $this->recordError($e);
            return false;
        }
    }

    /**
     * 获取指定sql所有数据
     */
    public function get_sql_all($sql)
    {
        try{
            $data = Capsule::connection($this->connection_name)->select($sql);
            $data = json_decode(json_encode($data), true);
            return $data;
        }catch (QueryException $e) {
            $this->recordError($e);
            return false;
        }
    }

    /**
     * 指定字段
     * @param string $fields
     * @return $this
     */
    public function field($fields = '*')
    {
        $this->fields = $fields;
        $this->fields = explode(",",$this->fields);
        if(count($this->fields) == 1)
        {
            $this->fields = $fields;
        }
        return $this;
    }

    /**
     * get_one
     */
    public function get_one($where = [])
    {   
        try{
            $tmp = self::where($where)->select($this->fields)->first();
            $this->fields = '*';
            if($tmp)
            {
                $tmp = $tmp->toArray();
            }
            return $tmp;
        }catch (QueryException $e) {
            $this->recordError($e);
            return false;
        }                                              
        
    }

    /**
     * 返回所有数据
     */
    public function get_all($where = [])
    {
        try{
            // $tmp = parent::select($this->table_name,$this->fields,$where);
            $tmp = self::where($where)->select($this->fields)->get();
            $this->fields = '*';
            if($tmp)
            {
                $tmp = $tmp->toArray();
            }
            return $tmp;
        }catch (QueryException $e) {
            $this->recordError($e);
            return false;
        }
    }

    /**
     * 列出列表
     */
    public function list($where = [])
    {
        $limit = [($this->page-1) * $this->limit,$this->limit];
        $where['LIMIT'] = $limit;
        $data = $this->get_all($where);
        unset($where['LIMIT']);
        $total = self::where($where)->count();
        return [
            'list' => $data,
            'total' => $total
        ];
    }

    /**
     * 插入新数据
     * @param $attrs
     */
    public function insert1($attrs = '')
    {
        if(empty($attrs) && !empty($this->attr) )
        {
            $attrs = $this->attr;
        }
        try{
            // insertGetId | insert
            $tmp = self::insertGetId($attrs);
            return $tmp;
        }catch (QueryException $e) {
            $this->recordError($e);
            return false;
        }
    }

    /**
     * 更新数据
     * @param $attrs
     */
    public function update1($attrs,$where){
        try{
            //这个里where
            $data = self::where($where)->update($attrs);
            return $data;
            // return  $data->rowCount();
        }catch (QueryException $e) {
            $this->recordError($e);
            return false;
        }
    }
    
	/**
     * 执行sql
     * @param $attrs
     */
    public function query1($sql)
    {
        try{
            $result = Capsule::connection($this->connection_name)->statement($sql);
            return $result;
        }catch (QueryException $e) {
            $this->recordError($e);
            return false;
        }
        
    }

    /**
     * 删除数据
     * 只针对id处理
     * @param $id
     */
    public function delete1($id){
        try{
            $status = self::where(['id'=>$id])->delete();
            return $status;
        }catch (QueryException $e) {
            $this->recordError($e);
            throw $e;
        }
        
    }

    /**
     * 解释排序字段
     * game_id|ascend  字段|升降  ascend descend
     */
    public function orderField($sort_field = '')
    {
        $sort_field = explode("_",$sort_field);
        if(empty($sort_field) || !isset($sort_field['1']))
        {
            return '';
        }
        if($sort_field['1'] == 'ascend')
        {
            $sort_type = 'ASC';
        }else{
            $sort_type = 'DESC';
        }
        $order[$sort_field['0']] = $sort_type;
        return $order;
    }

}