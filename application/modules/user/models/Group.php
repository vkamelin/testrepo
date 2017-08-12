<?php
/**
 * @author Patsura Dmitry https://github.com/ovr <talk@dmtry.me>
 */

namespace User\Model;

use \Phalcon\Mvc\Model;

/**
 * Class Group
 * @package User\Model
 *
 * @Source("User_Groups")
 */
class Group extends Model
{
    /**
     * @Identity
     * @GeneratedValue
     * @Column(type="integer")
     */
    public $id;

    /**
     * @Column(type="string")
     */
    public $name;
    
    public function initialize()
    {
        $this->setSource('Users_Groups');
    }
}
