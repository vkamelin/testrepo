<?php

namespace Frontend\Controller;

use Frontend\Controller\BaseController;
use Frontend\Model\State;
use Frontend\Model\Product;
use Phalcon\Translate\Adapter\NativeArray;

class IndexController extends BaseController
{

    private $errors = [];

    public function initialize()
    {
        $this->translation();
    }

    public function indexAction()
    {
        
        $this->view->homepage = true;

    }

    public function statesAction()
    {

        $this->view->disable();

        $data = array('status' => 'error');

        if ($this->request->getHttpHost() == HOST && $this->request->isPost() && $this->request->isAjax()) {

            $states = State::findByCountryId($this->request->getPost('countryId'))->toArray();

            if ($states) {
                $data['status'] = 'ok';
                $data['states'] = $states;
            }

        }

        $response = new \Phalcon\Http\Response();

        $response->setJsonContent($data);
        $response->send();

    }

    private function validate()
    {
        $validation = new Validation();

        $validation->add(
            ['firstname', 'lastname', 'address', 'country', 'zipcode', 'state', 'city', 'phone', 'email'],
            new PresenceOf([
                'message' => 'The :field field is required'
            ])
        );

        $validation->add(
            'email',
            new Email(['message' => 'The e-mail is not valid'])
        );

        $messages = $validation->validate($_POST);

        if (count($messages)) {
            foreach ($messages as $message) {
                $field = $message->getField();
                $this->errors[$field] = $message->getMessage();
            }
            return false;
        } else {
            return true;
        }
    }
    
    public function testAction()
    {
        
        var_dump($_SERVER); exit();
        
    }
}
