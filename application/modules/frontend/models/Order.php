<?php

namespace Frontend\Model;

use \Phalcon\Mvc\Model;

/**
 * Class Order
 * @package Frontend\Model
 */

class Order extends Model
{
    /**
     * @Identity
     * @Primary
     * @Column(type="integer", nullable=false)
     * @var integer
     */
    public $id;

    /**
     * @Column(type="string", length="50")
     * @var string
     */
    public $firstname;

    /**
     * @Column(type="string", length="50")
     * @var string
     */
    public $lastname;

    /**
     * @Column(type="integer", length="10")
     * @var string
     */
    public $country;

    /**
     * @Column(type="string", length="16")
     * @var string
     */
    public $zipcode;

    /**
     * @Column(type="integer", length="10")
     * @var string
     */
    public $state;

    /**
     * @Column(type="string", length="30")
     * @var string
     */
    public $city;

    /**
     * @Column(type="string", length="100")
     * @var string
     */
    public $address;

    /**
     * @Column(type="string", length="50", nullable=true)
     * @var string
     */
    public $phone;

    /**
     * @Column(type="string", length="50")
     * @var string
     */
    public $email;

    /**
     * @Column(type="integer", length="1", nullable=true)
     * @var string
     */
    public $product;

    /**
     * @Column(type="string", length="9", nullable=true)
     * @var float
     */
    public $total;

    /**
     * @Column(type="string", length="50", nullable=true)
     * @var string
     */
    public $trackNumber;

    /**
     * @Column(type="integer", length="1", nullable=true)
     * @var string
     */
    public $trackNumberSended;
    
    /**
     * @Column(type="string", length="19", nullable=true)
     * @var string
     */
    public $trackNumberDate;

    /**
     * @Column(type="integer", length="1", nullable=true)
     * @var string
     */
    public $status;
    
    /**
     * @Column(type="string", length="36", nullable=true)
     * @var string
     */
    public $paymentId;
    
    /**
     * @Column(type="string", length="100", nullable=true)
     * @var string
     */
    public $paymentError;
    
    /**
     * @Column(type="string", length="10000", nullable=true)
     * @var string
     */
    public $paymentData;

    /**
     * @Column(type="string", length="19", nullable=true)
     * @var float
     */
    public $dateCreated;

    /**
     * @Column(type="string", length="19", nullable=true)
     * @var float
     */
    public $dateModified;

    public function initialize()
    {

        $this->setSource('Orders');

        $this->belongsTo(
            'country',
            '\\Frontend\\Model\\Country',
            'id',
            [
                'alias' => 'Country',
            ]
        );

        $this->belongsTo(
            'state',
            '\\Frontend\\Model\\State',
            'id',
            [
                'alias' => 'State',
            ]
        );

        $this->belongsTo(
            'product',
            '\\Frontend\\Model\\Product',
            'id',
            [
                'alias' => 'Product',
            ]
        );
        
        if (isset($this->status) && !is_int($this->status)) {
            
            switch ($this->status) {
                case 'created': $this->status = 0;
                    break;
                case 'paid': $this->status = 1;
                    break;
                case 'sended': $this->status = 2;
                    break;
                case 'delivered': $this->status = 3;
                    break;
            }
            
        }

    }

    public function beforeUpdate()
    {

        $this->dateModified = date('Y-m-d H:i:s');
        
        if (empty($this->trackNumberDate)) {
            $this->trackNumberDate = NULL;
        }

    }

    public function beforeCreate()
    {

        $this->dateCreated = date('Y-m-d H:i:s');
        $this->dateModified = date('Y-m-d H:i:s');
        $this->trackNumberDate = NULL;

    }
    
}
