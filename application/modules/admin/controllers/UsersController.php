<?php

namespace Admin\Controller;

use Phalcon\Filter;
use App\Filters\Order;
use DataTables\DataTable;
use Admin\Model\User;
use Admin\Form\UserForm;

class UsersController extends IndexController
{
    
    public function initialize()
    {
        $this->dashboard['title'] = $this->view->t->_('users');
        $this->breadcrumbs[] = [
            'name' => $this->view->t->_('users'),
            'href' => '/admin/users'
        ];
    }
    
    public function indexAction()
    {
        $this->dashboard['subtitle'] = $this->view->t->_('all_users');
        $this->view->dashboard = $this->dashboard;
        $this->view->breadcrumbs = $this->breadcrumbs;
    }
    
    public function formAction($id = 0)
    {
        if ($id == 0) {
            $this->dashboard['subtitle'] = $this->view->t->_('user_new');
        } else {
            $this->dashboard['subtitle'] = $this->view->t->_('user_edit');
        }
        
        $this->view->dashboard = $this->dashboard;
        $this->view->breadcrumbs = $this->breadcrumbs;
        
        if ($id == 0) {
            $form = new UserForm(new User(), ['edit' => false]);
        } else {
            $form = new UserForm(User::findFirst($id), ['edit' => true]);
        }

        if ($this->request->isPost()) {
            
            try {
                if ($form->isValid($this->request->getPost()) && $this->checkToken()) {
                    if ($id == 0) {
                        $user = new User();
                        $user->create($this->request->getPost());
                    } else {
                        $user = User::find($id);
                        $user->update($this->request->getPost());
                    }
    
                    // $user->save($this->request->getPost());

                    if ($id == 0) {
                        $this->response->redirect(array('for' => 'admin/users/form'));
                    } else {
                        $this->response->redirect(array('for' => 'admin/users/form/' . $id));
                    }
                    
                } else {
                    $messages = $form->getMessages();
    
                    foreach ($messages as $message) {
                        echo $message; // exit();
                        // echo $message, "<br>";
                        $this->flashSession->error($message . ' ' . $this->security->getSessionToken());
                    }
                    
                    // var_dump($this->flashSession->output()); exit();
                }
            } catch (\Exception $e) {
                // $this->flashSession->error($e->getMessage());
            }
        }
        
        if ($id) {
            $this->view->user = User::findFirst($id);
        }

        $this->view->form = $form;
    }
    
    public function jsonAction()
    {
        $this->view->disable();
        
        if ($this->request->isPost() && $this->request->isAjax()) {
            $builder = $this->modelsManager->createBuilder()
                ->columns('id, email, status')
                ->from('Admin\Model\User');

            $dataTables = new DataTable([], []);
            $dataTables->fromBuilder($builder)->sendResponse();
        } else {
            echo 'Invalid token';
        }
    }
    
}
