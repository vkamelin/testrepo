<?php

use Phalcon\Mvc\Router;
use Phalcon\Mvc\View;
use Phalcon\Crypt;
use Phalcon\Security;
use Phalcon\Http\Response\Cookies;
use Phalcon\Assets\Manager as AssetsManager;
use Phalcon\Translate\Adapter\NativeArray;

use Phalcon\Mvc\Model\MetaData\Files as FilesMetaData;
// use Phalcon\Mvc\Model\MetaData\Memory as MemoryMetaData;
use Phalcon\Mvc\Model\MetaData\Strategy\Annotations as StrategyAnnotations;

use Phalcon\Flash\Direct as FlashDirect;
use Phalcon\Flash\Session as FlashSession;

date_default_timezone_set('US/Eastern');
setlocale(LC_ALL, 'ru_RU.UTF-8');

if (PHP_VERSION_ID < 50600) {
    iconv_set_encoding('internal_encoding', 'UTF-8');
}

$parameters = include_once __DIR__ . '/parameters.php';

return array(
    'parameters' => &$parameters,
    'services' => array(
        'db' => array(
            'class' => '\Phalcon\Db\Adapter\Pdo\Mysql',
            '__construct' => array(
                $parameters['db']
            )
        ),
        'logger' => array(
            'class' => '\Phalcon\Logger\Adapter\File',
            '__construct' => array(
                APPLICATION_PATH . '/logs/' . APPLICATION_ENV . '.log'
            )
        ),
        'url' => array(
            'class' => '\Phalcon\Mvc\Url',
            'shared' => true,
            'parameters' => $parameters['url']
        ),
        'tag' => array(
            'class' => '\App\Tag'
        ),
        'modelsMetadata' => array(
            'class' => function () {
                $metaData = new FilesMetaData(['metaDataDir' => APPLICATION_PATH . '/cache/metadata/']);
                // $metaData = new MemoryMetaData();
                $metaData->setStrategy(new StrategyAnnotations());

                return $metaData;
            }
        ),
        'dispatcher' => array(
            'class' => function ($application) {
                $evManager = $application->getDI()->getShared('eventsManager');

                $evManager->attach('dispatch:beforeException', function ($event, $dispatcher, $exception) use (&$application) {

                    if (!class_exists('Frontend\Module')) {
                        include_once APPLICATION_PATH . '/modules/frontend/Module.php';
                        $module = new Frontend\Module();
                        $module->registerServices($application->getDI());
                        $module->registerAutoloaders($application->getDI());
                    }

                    /**
                     * @var $dispatcher \Phalcon\Mvc\Dispatcher
                     */
                    $dispatcher->setModuleName('frontend');

                    $dispatcher->setParam('error', $exception);
                    $dispatcher->forward(
                        array(
                            'namespace' => 'Frontend\Controller',
                            'module' => 'frontend',
                            'controller' => 'error',
                            'action'     => 'index'
                        )
                    );
                    return false;
                });

                $dispatcher = new \Phalcon\Mvc\Dispatcher();
                $dispatcher->setEventsManager($evManager);

                return $dispatcher;
            }
        ),
        'modelsManager' => array(
            'class' => function ($application) {
                $modelsManager = new \Phalcon\Mvc\Model\Manager();

                return $modelsManager;
            }
        ),
        'router' => array(
            'class' => function ($application) {
                $router = new Router(false);

                $router->removeExtraSlashes(true);

                $router->add('/', array(
                    'module' => 'frontend',
                    'controller' => 'index',
                    'action' => 'index'
                ))->setName('default');
                
                

                foreach ($application->getModules() as $key => $module) {
                    $router->add('/'.$key.'/:params', array(
                        'module' => $key,
                        'controller' => 'index',
                        'action' => 'index',
                        'params' => 1
                    ))->setName($key);

                    $router->add('/'.$key.'/:controller/:params', array(
                        'module' => $key,
                        'controller' => 1,
                        'action' => 'index',
                        'params' => 2
                    ));

                    $router->add('/'.$key.'/:controller/:action/:params', array(
                        'module' => $key,
                        'controller' => 1,
                        'action' => 2,
                        'params' => 3
                    ));
                }
                
                
                
                $router->add('/:controller/:action/:params', array(
                    'module' => 'frontend',
                    'controller' => 1,
                    'action' => 2,
                    'params' => 3
                ));
                
                $router->add('/:controller/:action', array(
                    'module' => 'frontend',
                    'controller' => 1,
                    'action' => 2,
                ));
                
                $router->add('/:controller', array(
                    'module' => 'frontend',
                    'controller' => 1,
                    'action' => 'index',
                ));
                
                $router->add('/([a-zA-Z0-9\_\-]+)', array(
                    'module' => 'frontend',
                    'controller' => 'page',
                    'action' => 'index',
                    'slug' => 1
                ));

                $router->notFound(array(
                    'module' => 'frontend',
                    'namespace' => 'Frontend\Controller',
                    'controller' => 'index',
                    'action' => 'error'
                ));

                return $router;
            },
            'parameters' => array(
                'uriSource' => Router::URI_SOURCE_SERVER_REQUEST_URI
            ),
        ),
        'view' => array(
            'class' => function () {
                $class = new View();
                $class->registerEngines(array(
                    '.phtml' => 'Phalcon\Mvc\View\Engine\Php'
                ));

                return $class;
            },
            'parameters' => array(
                'layoutsDir' => APPLICATION_PATH . '/layouts/'
            )
        ),
        'simpleView' => array(
            'class' => function () {
                $class = new Phalcon\Mvc\View\Simple();
                
                return $class;
            }
        ),
        'crypt' => array(
            'class' => function () {
                $class = new Crypt();
                $class->setKey('TH]NW7*~Uw7(4qmd');

                return $class;
            }
        ),
        'security' => array(
            'class' => function () {
                $class = new Security();
                $class->setWorkFactor(12);

                return $class;
            }
        ),
        'session' => array(
            'class' => function () {
                $class = new Phalcon\Session\Adapter\Files();
                $class->start();

                return $class;
            }
        ),
        'flash' => array(
            'class' => function () {
                return new FlashDirect();
            }
        ),
        'flashSession' => array(
            'class' => function () {
                return new FlashSession(
                    [
                        "error"   => "alert alert-danger",
                        "success" => "alert alert-success",
                        "notice"  => "alert alert-info",
                        "warning" => "alert alert-warning",
                    ]
                );
            }
        ),
        'cookies' => array(
            'class' => function () {
                $class = new Cookies();

                return $class;
            }
        ),
        'auth' => array(
            'class' => '\App\Service\Auth'
        ),
        'settings' => array(
            'class' => function () {
                $class = new \Phalcon\Config\Adapter\Php(__DIR__ . '/settings.php');

                return $class;
            }
        ),
        /* 'debugbar' => array(
            'class' => function ($application) {
                if (PHALCONDEBUG == true && APPLICATION_ENV == 'development') {
                    $class = new \PDW\DebugWidget($application->getDI());
                }

                return $class;
            }
        ) */
    ),
    'application' => array(
        'modules' => array(
            'frontend' => array(
                'className' => 'Frontend\Module',
                'path' => APPLICATION_PATH . '/modules/frontend/Module.php',
            ),
            'admin' => array(
                'className' => 'Admin\Module',
                'path' => APPLICATION_PATH . '/modules/admin/Module.php',
            ),
            'api' => array(
                'className' => 'Api\Module',
                'path' => APPLICATION_PATH . '/modules/api/Module.php',
            ),
            'user' => array(
                'className' => 'User\Module',
                'path' => APPLICATION_PATH . '/modules/user/Module.php',
            ),
            'oauth' => array(
                'className' => 'OAuth\Module',
                'path' => APPLICATION_PATH . '/modules/oauth/Module.php',
            )
        )
    )
);
