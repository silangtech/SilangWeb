<?php
namespace sapp\http\api\middleware;

class SetCorsMiddleware
{
    public function handle($c)
    {
        //这里不用new 
        $domainName = '*';
        if (isset($c->request->server['referer']) && strstr($c->request->server['referer'],'.cn')){
            $domainName = $c->request->server['referer'];
        }
        if ($domainName != '*'){
            $strdomain = explode("cn/", $domainName);
            $domainName = $strdomain[0]."cn";
        }
        $c->repsonse->setCors($domainName);

        $response = \SilangPHP\Route::next($c);

        return $response;
    }
}
