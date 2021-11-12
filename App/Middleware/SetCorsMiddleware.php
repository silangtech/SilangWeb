<?php
namespace App\Middleware;
use Closure;
use SilangPHP\Response;
use SilangPHP\SilangPHP;

class SetCorsMiddleware
{
    public function handle($c)
    {
        $domainName = '*';
        $c->response->setCors($domainName);
        $response = \SilangPHP\Route::next($c);
        return $response;
    }
}
