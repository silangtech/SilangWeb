<?php
namespace App;
use \SilangPHP\Route;

class Router
{
    public static function initialize()
    {
        Route::addRoute('GET', '/', '\\App\\Controller\\IndexController@Index');
        Route::addRoute('GET', '/home', '\\App\\Controller\\IndexController@Index');

        Route::addGroup('/silangphp', function(){
            Route::addRoute('GET', '/{id:\d+}',function ($id='1',$c=null){
                $c->response->write($id);
            });
        },
        function($c){
            echo '我是一个中间件_start'.PHP_EOL;
            \SilangPHP\Route::next($c);
            echo '我是一个中间件_end'.PHP_EOL;
        }
        );
    }
}