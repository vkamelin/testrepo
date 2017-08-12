<?php

namespace Frontend\Model;
use \Phalcon\Mvc\Model;

/**
 * Class Plan
 * @package Frontend\Model
 *
 * @Source("plans")
 *
 */

class Product extends Model
{
    /**
     * @Identity
     * @Primary
     * @Column(type="integer")
     * @var integer
     */
    public $id;

    /**
     * @Column(type="string", length=50)
     * @var string
     */
    public $name;
    
    /**
     * @Column(type="paypal", length=30)
     * @var string
     */
    public $paypal;

    /**
     * @Column(type="string", length=8)
     * @var string
     */
    public $price;

    /**
     * @Column(type="string", length=8)
     * @var string
     */
    public $shippingPrice;

    public function initialize()
    {
        $this->setSource('Products');
    }

}
