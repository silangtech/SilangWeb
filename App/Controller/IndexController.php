<?php
namespace App\Controller;

class IndexController extends \App\Support\Controller
{
    public function index($c, $id = '1234')
    {
        $c->response->write('SilangPHP');   
    }
}