<?php
namespace sapp\http\api\model;

class IndexModel extends \sapp\support\Model
{
    protected $connection = 'default';
    protected $table = 'users';
    public $primaryKey = 'userid';
}