<?php
/**
 * 普通模式下的入口
 */
define("PS_ROOT_PATH",       dirname(dirname(__FILE__)));
define("PS_CONFIG_PATH",     PS_ROOT_PATH."/Config/");
define("PS_RUNTIME_PATH",	 PS_ROOT_PATH."/Runtime/");
require_once(PS_ROOT_PATH."/vendor/autoload.php");

\App\Router::initialize();

\SilangPHP\SilangPHP::run();