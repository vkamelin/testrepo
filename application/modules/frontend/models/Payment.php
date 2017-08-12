<?php

namespace Frontend\Model;
use \Phalcon\Mvc\Model;

/**
 * Class Payment
 * @package Frontend\Model
 */

class Payment extends Model
{

    /**
     * @Column(type="string", length=100)
     * @var string
     */
    public $name;
    
    /**
     * @Column(type="string", length=100)
     * @var string
     */
    public $class;

    public function initialize()
    {
        $this->setSource('Payments');
    }

}
