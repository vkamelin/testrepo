<?php

namespace App\Payment;

use App\Curl;
use \Frontend\Model\Order;

class LatAmPacific
{

    public $errors = array();
    public $testing = true;

    public function __construct()
    {
        $this->testing = true;
    }

    public function pay()
    {
        
        $curl = new Curl();

        if ($this->testing) {
            $curl->request('http://www.latampacific.com:8080/post/processing/process_cc.php');
        } else {
            $curl->request('http://www.latampacific.com:8080/post/processing/process_cc.php');
        }
        
    }

}
