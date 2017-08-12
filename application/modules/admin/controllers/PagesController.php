<?php

namespace Admin\Controller;

use Phalcon\Filter;
use DataTables\DataTable;
use Frontend\Model\Page;
use Admin\Form\PageForm;

class PagesController extends IndexController
{
    
    public function initialize()
    {
        $this->dashboard['title'] = _('Pages');
        $this->breadcrumbs[] = [
            'name' => _('Pages'),
            'href' => '/admin/pages'
        ];
    }
    
    public function indexAction()
    {
        $this->dashboard['subtitle'] = _('All Pages');
        $this->view->dashboard = $this->dashboard;
        $this->view->breadcrumbs = $this->breadcrumbs;
    }
    
    public function formAction($id = 0)
    {
        $this->dashboard['subtitle'] = _('Edit Page');
        
        $this->view->dashboard = $this->dashboard;
        $this->view->breadcrumbs = $this->breadcrumbs;
        
        if ($id == 0) {
            $this->response->setStatusCode(404, 'Not Found');
        } else {
            $form = new OrderForm(Order::findFirst($id));
        }

        if ($this->request->isPost()) {
            
            try {
                if ($form->isValid($this->request->getPost()) && $this->checkToken()) {
                    
                    $order = Order::findFirst($id);
                    
                    $order->update($this->request->getPost());
                    
                    
                } else {
                    $messages = $form->getMessages();
    
                    foreach ($messages as $message) {
                        echo $message;
                        $this->flashSession->error($message . ' ' . $this->security->getSessionToken());
                    }
                }
            } catch (\Exception $e) {
                $this->flashSession->error($e->getMessage());
            }
            
        }
        
        $order = Order::findFirst($id);
        
        $this->view->order = Order::findFirst($id);
        $this->view->product = $order->getProduct();

        $this->view->form = $form;
    }
    
    public function jsonAction()
    {
        $this->view->disable();
        
        if (isset($_POST['search']['value'])) {
            switch ($_POST['search']['value']) {
                case 'created':
                    $_POST['search']['value'] = 0;
                    $statusSearch = true;
                    break;
                case 'paid':
                    $_POST['search']['value'] = 1;
                    $statusSearch = true;
                    break;
                case 'sended':
                    $_POST['search']['value'] = 2;
                    $statusSearch = true;
                    break;
                case 'delivered':
                    $_POST['search']['value'] = 3;
                    $statusSearch = true;
                    break;
                default:
                    $statusSearch = false;
            }
            
            if ($statusSearch) {
                $columnsNumber = count($_POST['columns']);
                
                for ($i = 0; $i < $columnsNumber; $i++) {
                    if ($i != 6) {
                        $_POST['columns'][$i]['searchable'] = false;
                    }
                }
            }
        }
        
        if ($this->request->isPost() && $this->request->isAjax()) {
            $builder = $this->modelsManager->createBuilder()
                ->columns('id, firstname, lastname, email, dateCreated, dateModified, status, paymentError')
                ->from('Frontend\Model\Order');

            $dataTables = new DataTable([], []);
            $dataTables->fromBuilder($builder)->sendResponse();
        } else {
            echo 'Invalid token';
        }
    }
    
}
