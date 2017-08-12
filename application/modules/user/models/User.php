<?php
/**
 * @author Patsura Dmitry https://github.com/ovr <talk@dmtry.me>
 */

namespace User\Model;

use \Phalcon\Mvc\Model;
use \User\Model\Group;

/**
 * Class User
 * @package User\Model
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
        $this->setSource('User');

        $this->belongsTo(
            'group_id',
            '\User\Model\Group',
            'id',
            [
                'alias' => 'Group',
            ]
        );
    }

    public function getGroup()
    {
        return $this->getRelated('Group');
    }

    public function getFullname()
    {
        return $this->email;
    }

}
