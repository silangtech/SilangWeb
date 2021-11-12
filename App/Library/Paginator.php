<?php
namespace App\Library;
/**
 * list data
 */
class Paginator{
    
    public $currentPage = 1;
    public $pageSize = 25;
    public $param = [];
    public $fields = "*";
    public $joins = [];
    public function __construct($model,$param,$fields = ['*'])
    {
        $this->model = $model;
        // $this->param = $param;
        if(isset($param['currentPage']))
        {
            $this->currentPage = $param['currentPage'];
        }
        if(isset($param['pageSize']))
        {
            $this->currentSize = $param['pageSize'];
        }
        if(isset($param['paramdata']))
        {
            $this->param = $param['paramdata'];
        }
        $this->model = $this->model::select($fields);
        if($this->param)
        {
            $this->model = $this->model->where($this->param);
        }
    }

    public function setLeftJoin($table, $key1, $op = '=', $key2)
    {
        $this->model = $this->model->leftJoin($table, $key1, $op, $key2);
    }

    /**
     * 获取结果
     */
    public function getResult()
    {
        $offset = ($this->currentPage -1) * $this->pageSize;
        return $this->model->offset($offset)->limit($this->pageSize)->get();
    }

    /**
     * 获取总数
     *
     * @return void
     */
    public function getTotal()
    {
        $count = $this->model->count();
        return $count;
    }

    /**
     * 获取列表
     */
    public function getList()
    {
        $data['totalcount'] = $this->getTotal();
        $data['list'] = $this->getResult();
        if($data['list'])
        {
            $data['list'] = $data['list']->toArray();
        }
        return $data;
    }

}

?>