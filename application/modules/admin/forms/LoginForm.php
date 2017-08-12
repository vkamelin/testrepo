<?php

namespace Admin\Form;

use Phalcon\Forms\Element\Text;
use Phalcon\Forms\Element\Password;
use Phalcon\Forms\Element\Submit;
use Phalcon\Forms\Element\Check;
use Phalcon\Forms\Element\Hidden;
use Phalcon\Validation\Validator\PresenceOf;
use Phalcon\Validation\Validator\Email;
use Phalcon\Validation\Validator\Identical;

/**
 * Class Login
 * @package Admin\Form
 */
class LoginForm extends \Phalcon\Forms\Form
{

    public function initialize()
    {
        $email = new Text('email', array(
            'placeholder' => 'Email',
            'class' => 'form-control'
        ));
        $email->addValidators(array(
            new PresenceOf(array(
                'message' => 'The e-mail is required'
            )),
            new Email(array(
                'message' => 'The e-mail is not valid'
            ))
        ));
        $this->add($email);

        $password = new Password('password', array(
            'placeholder' => 'Password',
            'class' => 'form-control'
        ));
        $password->addValidator(new PresenceOf(array(
            'message' => 'The password is required'
        )));
        $this->add($password);

        $remember = new Check('remember', array(
            'value' => 'yes'
        ));
        $remember->setLabel('Remember me');
        $this->add($remember);

        $this->add(new Submit('Sing in', array(
            'class' => 'btn btn-lg btn-primary btn-block'
        )));
    }
}
