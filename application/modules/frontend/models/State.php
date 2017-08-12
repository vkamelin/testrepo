<?php

namespace Frontend\Model;
use \Phalcon\Mvc\Model;

/**
 * Class State
 * @package Frontend\Model
 *
 * @Source("states")
 *
 */

class State extends Model
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
    public $countryId;

    /**
     * @Column(type="string", length=30)
     * @var string
     */
    public $name;
    
    /**
     * @Column(type="string", length=3)
     * @var string
     */
    public $iso;

    public function initialize()
    {
        $this->setSource('States');
    }

}
