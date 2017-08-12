<?php

namespace App\Payment;

use App\Curl;
use \Frontend\Model\Order;

class RisingSunPayment
{

    public $errors = '';
    public $testing = true;
    public $status = false;
    public $statusMessage = '';
    
    public $mid = 'MD170328143449563';
    public $accountid = 'AC170328143704694';
    public $username = '4nHSy';
    public $password = 'sANyIN4m7G';
    public $secret = '8be143a578f55cb3d6eb39b9c67bb5cb';

    public function __construct()
    {
    }

    public function pay($paymentData)
    {

        $curl = new Curl();
        
        $trackid = $paymentData['orderId'] . time();

        $requestData = [
            'req_type'        => 'CAPTURE',
            'req_mid'         => $this->mid,
            'req_accountid'   => $this->accountid,
            'req_username'    => $this->username,
            'req_password'    => $this->password,
            'req_trackid'     => $trackid,
            'req_amount'      => $paymentData['total'],
            'req_currency'    => 'USD',
            'req_cardnumber'  => $paymentData['cardNumber'],
            'req_cardtype'    => $paymentData['cardType'],
            'req_yyyy'        => $paymentData['cardYear'],
            'req_mm'          => $paymentData['cardMonth'],
            'req_cvv'         => $paymentData['cardCVV'],
            'req_firstname'   => $paymentData['firstname'],
            'req_lastname'    => $paymentData['lastname'],
            'req_address'     => $paymentData['address'],
            'req_city'        => $paymentData['city'],
            'req_statecode'   => $paymentData['state'],
            'req_countrycode' => $paymentData['country'],
            'req_zipcode'     => $paymentData['zipcode'],
            'req_phone'       => '+79227400164',
            'req_email'       => $paymentData['email'],
            'req_ipaddress'   => $paymentData['ip'],
            'req_returnurl'   => 'https://garciniamegaslim.com/return/risingSunPayment',
            'req_remarks'     => '',
            'req_signature'   => md5('CAPTURE' . $this->mid . $this->accountid . $this->username . $this->password . $trackid . $this->secret),
        ];

        if ($this->testing) {
            $response = $curl->request('http://devsecure.rsppayment.com/services.rsp', $requestData);
        } else {
            $response = $curl->request('https://secure.rsppayment.com/services.rsp', $requestData);
        }
        
        parse_str(urldecode($response['content']), $data);
        
        // var_dump($data); exit();
        
        if ($data['res_code'] == 0) {
            
            return true;
            
        } else {
            
            if ($data['res_code'] == 2) {
            
                $this->status = 'pending';
                $this->statusMessage = $data['res_description'];
                return true;
            
            } else {
                
                $this->error = $data['res_description'];
                return false;
                
            }
            
        }

    }

}
