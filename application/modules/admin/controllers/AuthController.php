<?php

namespace Admin\Controller;

use Phalcon\Mvc\Controller;
use Admin\Form\LoginForm;
use Phalcon\Mvc\View;
use Admin\Model\User;

/**
 * Class AuthController
 * @package Admin\Controller
 */
class AuthController extends Controller
{

    public function loginAction()
    {
        $this->view->setLayout('admin/login');

        $form = new LoginForm();
        
        if ($this->request->isPost()) {
            try {
                $tokenKey = $this->session->get('$PHALCON/CSRF/KEY$');
                $tokenValue = $this->request->getPost($tokenKey);
                
                if ($form->isValid($this->request->getPost()) && $this->security->checkToken($tokenKey, $tokenValue)) {
                    $user = User::findFirstByEmail($this->request->getPost('email'));

                    if ($user && $this->security->checkHash($this->request->getPost('password'), $user->password)) {
                        $authService = $this->di->get('auth');
                        $authService->authByUser($user);
                        $this->response->redirect(array('for' => 'admin'));
                    } else {
                        $this->flashSession->error('User not exist');
                    }
                } else {
                    $messages = $form->getMessages();

                    foreach ($messages as $message) {
                        $this->flashSession->error($message);
                    }
                }
            } catch (\Exception $e) {
                $this->flashSession->error($e->getMessage());
            }
        }

        $this->view->form = $form;
    }

    /**
     * Logout action
     */
    public function logoutAction()
    {
        $this->view->setRenderLevel(View::LEVEL_NO_RENDER);

        $this->session->start();
        $this->session->destroy();

        $this->response->redirect();
    }
}
