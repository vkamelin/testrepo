<?php

namespace Frontend\Model;

use \Phalcon\Mvc\Model;

/**
 * Class Country
 * @package Frontend\Model
 *
 * @Source("countries")
 *
 */

class Country extends Model
{
    /**
     * @Identity
     * @Primary
     * @Column(type="integer")
     * @var integer
     */
    public $id;

    /**
     * @Column(type="string", length=3)
     * @var string
     */
    public $iso;

    /**
     * @Column(type="string", length=150)
     * @var string
     */
    public $name;

    /**
     * @Column(type="integer", length=10)
     * @var string
     */
    public $phoneCode;

    public function initialize()
    {
        $this->setSource('Countries');
    }

}
