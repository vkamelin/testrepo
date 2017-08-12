<?php

namespace Admin\Form;

use Phalcon\Forms\Element\Text;
use Phalcon\Forms\Element\Submit;
use Phalcon\Forms\Element\Check;
use Phalcon\Forms\Element\Hidden;
use Phalcon\Forms\Element\Select;
use Phalcon\Validation\Validator\PresenceOf;
use Frontend\Model\Payment;

/**
 * Class Order
 * @package Admin\Form
 */
class SettingsForm extends \Phalcon\Forms\Form
{
    public function initialize()
    {
        
        $payment = new Select('payment',
            Payment::find(),
            [
                'using' => ['class', 'name'],
                'class' => 'form-control select2',
                'value' => $this->settings->payment
            ]
        );
        $this->add($payment);

        $csrf = new Hidden('csrf', [
            'name' => $this->security->getTokenKey(),
            'value' => $this->security->getToken(),
            'id' => 'csrf'
        ]);
        
        $this->add($csrf);

        $this->add(new Submit('Sing in', array(
            'class' => 'btn btn-lg btn-primary btn-block'
        )));
    }
}
