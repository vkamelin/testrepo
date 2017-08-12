<?php

namespace Admin\Controller;

class TranslationsController extends IndexController
{
    
    private $translationsDir = APPLICATION_PATH . '/translations';
    
    public function initialize()
    {
        $this->dashboard['title'] = $this->view->t->_('translations');
        $this->breadcrumbs[] = [
            'name' => $this->view->t->_('translations'),
            'href' => '/admin/translations'
        ];
    }
    
    public function indexAction()
    {
        $files = scandir($this->translationsDir);
        
        $translations = array();
        
        foreach ($files as $file) {
            if ($file != '.' && $file != '..' && pathinfo($file, PATHINFO_EXTENSION) == 'php') {
                $translations[] = pathinfo($file, PATHINFO_FILENAME);
            }
        }
        
        $this->view->translations = $translations;
    }
    
    public function formAction($lang = false)
    {
        $this->dashboard['subtitle'] = $this->view->t->_('translations');
        $this->view->dashboard = $this->dashboard;
        $this->view->breadcrumbs = $this->breadcrumbs;
        
        $new = false;
        
        if ($lang) {
            $translation = include_once APPLICATION_PATH . '/translations/' . $lang . '.php';
        } else {
            $new = true;
            $translation = include_once APPLICATION_PATH . '/translations/en.php';
        }
        
        if ($this->request->isPost()) {
            
            foreach ($translation as $key => $val) {
                $postKey = str_replace('.', '-', $key);
                $translation[$key]['message'] = $this->request->getPost($postKey);
            }
            
            if ($lang) {
                $filePath = APPLICATION_PATH . '/translations/' . $lang . '.php';
            } else {
                $filePath = APPLICATION_PATH . '/translations/' . $this->request->getPost('lang') . '.php';
            }
            
            $fileData = '<?php' . PHP_EOL . PHP_EOL . 'return ' . var_export($translation, true) . ';';
                
            $fp = fopen($filePath, 'w');
            fwrite($fp, $fileData);
            fclose($fp);
            
        }
        
        $this->view->new = $new;
        $this->view->translation = $translation;
        
        
    }
    
}
