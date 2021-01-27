<?php
namespace App\Controller;

use App\Middleware\HelloMiddleware;
use App\Middleware\TestMiddleware;
use App\Model\IndexModel;
use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Container\Container;

class IndexController extends \SilangPHP\Controller
{
    public $middlewares = [HelloMiddleware::class,TestMiddleware::class];
    public $onlyAction = [];
    public $exceptAction = [];
    public function beforeAction($action = '')
    {

    }

    public function index(\SilangPHP\Request $request,$a = 'test')
    {

        $data = \SilangPHP\Tpl::display("index");
        return $data;
    }

    public function index2($tt = '123')
    {
        
    }

    public function sessiontest()
    {
        \SilangPHP\Session::start();
        \SilangPHP\Session::set("a1234",1234);
        $tmp = \SilangPHP\Session::get("a1234");
        var_dump($tmp);

        setcookie("test","1234");
        var_dump($_COOKIE);
    }


}