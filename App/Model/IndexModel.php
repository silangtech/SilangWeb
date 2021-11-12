<?php
namespace App\Model;

class IndexModel extends \App\Support\Model
{
    protected $connection = 'default';
    // public $table_name = 'users';
    protected $table = 'users';
    public $primaryKey = 'userid';
}