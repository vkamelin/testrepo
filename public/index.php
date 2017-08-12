<?php
/**
 * @author Patsura Dmitry https://github.com/ovr <talk@dmtry.me>
 */

use Phalcon\Di;

define('APPLICATION_PATH', dirname(dirname(__FILE__)) . '/application');
define('PUBLIC_PATH', dirname(__FILE__));
define('APPLICATION_ENV', getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'development');
defined('PHALCONDEBUG') || define('PHALCONDEBUG', false);
define('HOST', 'nostressfidget.com');

date_default_timezone_set('Europe/Riga');

require_once APPLICATION_PATH . '/../vendor/autoload.php';
$config = include APPLICATION_PATH . '/config/core.php';

// apc_store('test1', 'value1');
// var_dump(apc_fetch('test1'));

putenv('LC_ALL=ru_RU');
setlocale(LC_ALL, 'ru_RU');
bindtextdomain('messages', APPLICATION_PATH . '/locales');
textdomain('messages');

$application = new \Phalcony\Application(APPLICATION_ENV, $config);
$application->bootstrap()->run();
