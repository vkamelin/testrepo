<?php

namespace Admin\Form;

use Phalcon\Forms\Element\Text;
use Phalcon\Forms\Element\Submit;
use Phalcon\Forms\Element\Check;
use Phalcon\Forms\Element\Hidden;
use Phalcon\Forms\Element\Select;
use Phalcon\Validation\Validator\PresenceOf;
use Frontend\Model\Product;

/**
 * Class PlanForm
 * @package Admin\Form
 */
class ProductForm extends \Phalcon\Forms\Form
{
    public function initialize(Product $product)
    {
        
        $name = new Text('name', [
            'class' => 'form-control'
        ]);
        $name->addValidators(array(
            new PresenceOf(array(
                'message' => 'The name is required'
            ))
        ));
        $this->add($name);
        
        $paypal = new Text('paypal', [
            'class' => 'form-control'
        ]);
        $paypal->addValidators(array(
            new PresenceOf(array(
                'message' => 'The paypal is required'
            ))
        ));
        $this->add($paypal);
        
        $price = new Text('price', [
            'class' => 'form-control'
        ]);
        $price->addValidators(array(
            new PresenceOf(array(
                'message' => 'The price is required'
            ))
        ));
        $this->add($price);
        
        $shippingPrice = new Text('shippingPrice', [
            'class' => 'form-control'
        ]);
        $shippingPrice->addValidators(array(
            new PresenceOf(array(
                'message' => 'The shippingPrice is required'
            ))
        ));
        $this->add($shippingPrice);

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
