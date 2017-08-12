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
use Admin\Model\User;
use User\Model\Group;
use Frontend\Model\Country;

/**
 * Class User
 * @package Admin\Form
 */
class UserForm extends \Phalcon\Forms\Form
{
    public function initialize(User $user, array $options)
    {
        $this->add(new Hidden('id'));
        
        /*
        $group_id = new Select('group_id', Group::find(), [
            'using' => ['id', 'name'],
            'class' => 'form-control select2'
        ]);
        $this->add($group_id);
        */
        
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
            'using' => ['iso', 'name'],
            'class' => 'form-control select2'
        ]);
        $country->addValidators(array(
            new PresenceOf(array(
                'message' => 'The country is required'
            ))
        ));
        $this->add($country);
    
        $state = new Text('state', [
            'class' => 'form-control'
        ]);
        $state->addValidators(array(
            new PresenceOf(array(
                'message' => 'The state is required'
            ))
        ));
        $this->add($state);

        $membership = new Check('membership', [
            'value' => '1',
            'class' => 'minimal'
        ]);
        $membership->setLabel('Membership');
        $membership->addValidators(array(
            new PresenceOf(array(
                'message' => 'The membership is required'
            ))
        ));
        $this->add($membership);
    
        $membershipRenewal = new Check('membership_renewal', [
            'value' => '1',
            'class' => 'minimal'
        ]);
        $membershipRenewal->setLabel('Membership Renewal');
        $membershipRenewal->addValidators(array(
            new PresenceOf(array(
                'message' => 'The membership renewal is required'
            ))
        ));
        $this->add($membershipRenewal);
    
        $membershipExpiration = new Text('membership_expiration', [
            'class' => 'form-control datetime'
        ]);
        $membershipExpiration->addValidators(array(
            new PresenceOf(array(
                'message' => 'The membership expiration date is required'
            ))
        ));
        $this->add($membershipExpiration);

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
