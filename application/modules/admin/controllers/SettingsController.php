<?php

namespace Admin\Controller;

use Phalcon\Filter;
use Admin\Form\SettingsForm;

class SettingsController extends IndexController
{
    
    private $settings = [
        'payment'
    ];
    
    public function initialize()
    {
        $this->dashboard['title'] = $this->view->t->_('settings');
        $this->breadcrumbs[] = [
            'name' => $this->view->t->_('settings'),
            'href' => '/admin/settings'
        ];
    }
    
    public function indexAction()
    {
        $this->dashboard['subtitle'] = $this->view->t->_('settings');
        $this->view->dashboard = $this->dashboard;
        $this->view->breadcrumbs = $this->breadcrumbs;
        
        $form = new SettingsForm();
        
        if ($this->request->isPost()) {
            
            if ($form->isValid($this->request->getPost()) && $this->checkToken()) {
            
                $settings = [];
                
                foreach ($this->settings as $key) {
                    if (!empty($this->request->getPost($key))) {
                        $settings[$key] = $this->request->getPost($key);
                    }
                }
                
                $fileData = '<?php' . PHP_EOL . PHP_EOL . 'return ' . var_export($settings, true) . ';';
                
                $fp = fopen(APPLICATION_PATH . '/config/settings.php', 'w');
                fwrite($fp, $fileData);
                fclose($fp);
            
            }
            
        }
        
        $this->view->payment = $this->settings->payment;
        $this->view->form = $form;
    }
    
}
