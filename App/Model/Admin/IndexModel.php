<?php
namespace App\Model\Admin;

use SilangPHP\Model;

class IndexModel extends Model
{
    protected $connection = 'master';
    // public $table_name = 'users';
    protected $table = 'users';
    public static $primary_key = 'userid';
}