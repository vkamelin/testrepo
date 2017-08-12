<?php

namespace Frontend\Model;
use \Phalcon\Mvc\Model;

/**
 * Class City
 * @package Frontend\Model
 *
 * @Source("cities")
 *
 */

class City extends Model
{
    /**
     * @Identity
     * @Primary
     * @Column(type="integer")
     * @var integer
     */
    public $id;

    /**
     * @Column(type="integer", length=10)
     * @var string
     */
    public $stateId;

    /**
     * @Column(type="string", length=30)
     * @var string
     */
    public $name;

    public function initialize()
    {
        $this->setSource('Cities');
    }

}
