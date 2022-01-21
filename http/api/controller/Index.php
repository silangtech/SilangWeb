<?php
namespace sapp\http\api\index;

class index
{
    public function index($c, $id = '1234')
    {
        $c->response->write('SilangPHP');   
    }
}