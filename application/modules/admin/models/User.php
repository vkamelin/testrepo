<?php
/**
 * @author Patsura Dmitry https://github.com/ovr <talk@dmtry.me>
 */

namespace Admin\Model;

use \Phalcon\Mvc\Model;

/**
 * Class User
 * @package Admin\Model
 */

class User extends Model
{
    /**
     * @Identity
     * @Primary
     * @Column(type="integer")
     */
    public $id;

    /**
     * @Column(type="string", length=50)
     * @var string
     */
    public $email;

    /**
     * @Column(type="string", length=100, nullable=true)
     * @var string
     */
    public $password;

    /**
     * @Column(type="boolean", nullable=true)
     * @var string
     */
    public $status;

    public function initialize()
    {
        $this->setSource('Users');
    }
    
    public function getFullname()
    {
        return $this->email;
    }

}
