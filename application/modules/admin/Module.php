<?php
/**
 * @author Patsura Dmitry https://github.com/ovr <talk@dmtry.me>
 */

namespace Admin;

use Phalcon\DiInterface;
use Phalcon\Translate\Adapter\NativeArray;

use Phalcon\Logger;
use Phalcon\Events\Manager as EventsManager;
use Phalcon\Logger\Adapter\File as FileLogger;

class Module
{
    private $locale = 'en';
    
    public function registerAutoloaders(DiInterface $dependencyInjector = null)
    {
        $loader = new \Phalcon\Loader();
        $loader->registerNamespaces(array(
            'Admin\Controller' => APPLICATION_PATH . '/modules/admin/controllers/',
            'Admin\Model' => APPLICATION_PATH . '/modules/admin/models/',
            'Admin\Form' => APPLICATION_PATH . '/modules/admin/forms/'
        ));
        $loader->register();
    }

    public function registerServices(DiInterface $di = null)
    {
        $dispatcher = $di->get('dispatcher');
        $dispatcher->setDefaultNamespace('Admin\Controller');
        
        // get user identity
        $identity = $di->get('auth')->getIdentity();
        
        // get current controller and action names
        $router = $di->get('router');
        $controller = $router->getControllerName();
        $action = $router->getActionName();
        
        $simpleView = $di->get('simpleView');
        $simpleView->setViewsDir(APPLICATION_PATH . '/modules/admin/views/');
        $di->set('simpleView', $simpleView);

        $view = $di->get('view');
        // set admin layout
        $view->setLayout('admin');
        // set dirs
        $view->setViewsDir(APPLICATION_PATH . '/modules/admin/views/');
        $view->setLayoutsDir('../../common/layouts/');
        $view->setPartialsDir('../../common/partials/');
        // get identity for views
        $view->identity = $identity;
        // get translation messages
        $view->t = $this->getTranslation($di);
        // set current locale
        $view->locale = $this->locale;
        // dashboard titles
        $view->dashboard = [
            'title' => $view->t->_('dashboard'),
            'subtitle' => ''
        ];
        // breadcrumbs
        $view->breadcrumbs = [];
        
        /*$security = $di->get('security');
        $view->token = $security->getToken();
        $view->tokenKey = $security->getTokenKey();*/
        
        $di->set('view', $view);
        
        $assets = $di->get('assets');
        $assets->collection('header');
        $assets->collection('footer');
        
        // check if exists javascript for current controller and action
        if (file_exists('assets/js/' . $controller . '/' . $action . '.js')) {
            // add current controller and action javascript file to footer asstes collection
            $js = $assets->collection('footer');
            $js->addJs('assets/js/' . $controller . '/' . $action . '.js');
        }
        
        $di->set('assets', $assets);
        
        $connection = $di->getShared("db");
        
        $eventsManager = new EventsManager();

        $logger = new FileLogger(APPLICATION_PATH . '/logs/db.log');

        // Listen all the database events
        $eventsManager->attach('db', function ($event, $connection) use ($logger) {
            if ($event->getType() == 'beforeQuery') {
                $logger->log($connection->getSQLStatement(), Logger::INFO);
            }
        });
        
        $connection->setEventsManager($eventsManager);
    }
    
    /*
     * Get translation messages
     */
    protected function getTranslation(DiInterface $di = null)
    {
        $cookies = $di->get('cookies');
        
        if ($cookies->has('locale')) {
            $language = $cookies->get('locale')->getValue();
        } else {
            $request = $di->get('request');
            $language = substr($request->getBestLanguage(), 0, 2);
            $cookies->set('locale', $language, time() + 30 * 86400);
        }
        
        $this->locale = $language;
    
        require APPLICATION_PATH . '/locales/en.php';

        if (file_exists(APPLICATION_PATH . '/locales/' . $language . '.php')) {
            require APPLICATION_PATH . '/locales/' . $language . '.php';
        }

        return new NativeArray(
            array(
                'content' => $messages
            )
        );
    }
}
