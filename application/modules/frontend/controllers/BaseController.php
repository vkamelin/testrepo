<?php

namespace Frontend\Controller;

use Phalcon\Mvc\Controller;
use Phalcon\Translate\Adapter\NativeArray;

class BaseController extends Controller
{

    private $errors = [];

    public function translation()
    {
        $lang = $this->dispatcher->getParam('lang');
        //if (empty($lang)) { $lang = 'en'; }
        $this->view->lang = $lang;
        
        $messages = array();
        
        $translation = include_once APPLICATION_PATH . '/translations/en.php';

        if (file_exists(APPLICATION_PATH . '/translations/' . $lang . '.php')) {
            $translation = include_once APPLICATION_PATH . '/translations/' . $lang . '.php';
        }
        
        foreach ($translation as $key => $val) {
            $messages[$key] = $val['message'];
        }

        $this->view->t = new NativeArray(['content' => $messages]);
    }
    
}
