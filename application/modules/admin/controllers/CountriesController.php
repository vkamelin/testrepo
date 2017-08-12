<?php

namespace Admin\Controller;

use Phalcon\Filter;
use App\Filters\Order;
use User\Model\User;
use DataTables\DataTable;

class CountriesController extends IndexController
{
    
    public function initialize()
    {
        $this->dashboard['title'] = $this->view->t->_('users');
        $this->breadcrumbs[] = [
            'name' => $this->view->t->_('countries'),
            'href' => '/admin/countries'
        ];
    }
    
    public function indexAction()
    {
        $this->dashboard['subtitle'] = $this->view->t->_('all_countries');
        $this->view->dashboard = $this->dashboard;
        
        $this->view->breadcrumbs = $this->breadcrumbs;
    }
    
    public function jsonAction()
    {
        $this->view->disable();
        
        if ($this->request->isPost() && $this->request->isAjax() && $this->security->checkToken()) {
            $data = [
                'csrf_token_name' => $this->security->getTokenKey(),
                'csrf_token' => $this->security->getToken()
            ];
            
            $builder = $this->modelsManager->createBuilder()
                ->columns('id, firstname, lastname, email, dateCreated')
                ->from('User\Model\User');

            $dataTables = new DataTable([], $data);
            $dataTables->fromBuilder($builder)->sendResponse();
        } else {
            echo 'Invalid token';
        }
    }
    
}
