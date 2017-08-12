<?php

namespace Admin\Form;

use Phalcon\Forms\Element\Text;
use Phalcon\Forms\Element\Email as InputEmail;
use Phalcon\Forms\Element\Submit;
use Phalcon\Forms\Element\Check;
use Phalcon\Forms\Element\Hidden;
use Phalcon\Forms\Element\Select;
use Phalcon\Validation\Validator\PresenceOf;
use Phalcon\Validation\Validator\Email;
use Phalcon\Validation\Validator\Identical;
use Frontend\Model\Order;
use Frontend\Model\Country;
use Frontend\Model\State;

/**
 * Class Order
 * @package Admin\Form
 */
class OrderForm extends \Phalcon\Forms\Form
{
    public function initialize(Order $order)
    {
        $this->add(new Hidden('id'));
        
        $firstname = new Text('firstname', [
            'class' => 'form-control'
        ]);
        $firstname->addValidators(array(
            new PresenceOf(array(
                'message' => 'The firstname is required'
            ))
        ));
        $this->add($firstname);
        
        $lastname = new Text('lastname', [
            'class' => 'form-control'
        ]);
        $lastname->addValidators(array(
            new PresenceOf(array(
                'message' => 'The lastname is required'
            ))
        ));
        $this->add($lastname);
        
        $country = new Select('country', Country::find(), [
            'using' => ['id', 'name'],
            'class' => 'form-control select2'
        ]);
        $this->add($country);
        
        $zipcode = new Text('zipcode', [
            'class' => 'form-control'
        ]);
        $zipcode->addValidators(array(
            new PresenceOf(array(
                'message' => 'The zipcode is required'
            ))
        ));
        $this->add($zipcode);
        
        $state = new Select('state', State::findByCountryId($order->country), [
            'using' => ['id', 'name'],
            'class' => 'form-control select2'
        ]);
        $this->add($state);
        
        $city = new Text('city', [
            'class' => 'form-control'
        ]);
        $city->addValidators(array(
            new PresenceOf(array(
                'message' => 'The city is required'
            ))
        ));
        $this->add($city);
        
        $address = new Text('address', [
            'class' => 'form-control'
        ]);
        $address->addValidators(array(
            new PresenceOf(array(
                'message' => 'The address is required'
            ))
        ));
        $this->add($address);
        
        $phone = new Text('phone', [
            'class' => 'form-control'
        ]);
        $phone->addValidators(array(
            new PresenceOf(array(
                'message' => 'The phone is required'
            ))
        ));
        $this->add($phone);
        
        $email = new InputEmail('email', [
            'class' => 'form-control'
        ]);
        $email->addValidators(array(
            new PresenceOf(array(
                'message' => 'The e-mail is required'
            )),
            new Email(array(
                'message' => 'The e-mail is not valid'
            ))
        ));
        $this->add($email);
        
        $trackNumber = new Text('trackNumber', [
            'class' => 'form-control'
        ]);
        $trackNumber->addValidators(array(
            new PresenceOf(array(
                'message' => 'The track number is required'
            ))
        ));
        $this->add($trackNumber);
        
        $trackNumberDate = new Text('trackNumberDate', [
            'class' => 'form-control datetime'
        ]);
        $this->add($trackNumberDate);
        
        $status = new Select('status', 
            [
                '0' => 'Created',
                '1' => 'Paid',
                '2' => 'Sended',
                '3' => 'Delivered',
            ],
            [
                'class' => 'form-control select2'
            ]
        );
        $this->add($status);

        $csrf = new Hidden('csrf', [
            'name' => $this->security->getTokenKey(),
            'value' => $this->security->getToken(),
            'id' => 'csrf'
        ]);
        /*
        $csrf->addValidator(new Identical([
            'value' => $this->security->getSessionToken(),
            'message' => 'CSRF validation failed'
        ]));
        */
        $this->add($csrf);

        $this->add(new Submit('Sing in', array(
            'class' => 'btn btn-lg btn-primary btn-block'
        )));
    }
}
