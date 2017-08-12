<?php
/**
 * @author Patsura Dmitry https://github.com/ovr <talk@dmtry.me>
 */

namespace Frontend;

class Module
{
    public function registerAutoloaders()
    {
        $loader = new \Phalcon\Loader();
        $loader->registerNamespaces(array(
            'Frontend\Controller' => APPLICATION_PATH . '/modules/frontend/controllers/',
            'Frontend\Model' => APPLICATION_PATH . '/modules/frontend/models/',
        ));
        $loader->register();
    }

    public function registerServices($di)
    {
        $dispatcher = $di->get('dispatcher');
        $dispatcher->setDefaultNamespace('Frontend\Controller');

        /**
         * @var $view \Phalcon\Mvc\View
         */
        $view = $di->get('view');
        $view->setLayout('index');
        $view->setViewsDir(APPLICATION_PATH . '/modules/frontend/views/');
        $view->setLayoutsDir('../../common/layouts/');
        $view->setPartialsDir('../../common/partials/');
        $view->identity = $di->get('auth')->getIdentity();

        $di->set('view', $view);
        
        /*
        $response = $di->get('response');
        $response->setHeader('Cache-Control', 'max-age=3600');
        $response->setHeader('Pragma', 'cache');
        $response->setHeader('Expires', gmdate("D, d M Y H:i:s", time() + 3600) . ' GMT');
        
        $di->set('response', $response);
        */
    }
}
