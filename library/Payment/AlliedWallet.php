<?php

namespace App\Payment;

use App\Curl;
use \Frontend\Model\Order;
use \Frontend\Model\State;

class AlliedWallet
{

    public $error = '';
    public $testing = true;
    public $status = false;
    public $statusMessage = '';
    
    public $merchantId = '31523';
    public $siteId = '43505';
    public $OAuthToken = '';
    
    public $urlAuth = '';
    public $urlSale = '';

    public function __construct()
    {
        $this->testing = true;
        
        $this->urlAuth = 'https://api.alliedwallet.com/merchants/' . $this->merchantId . '/authorizetransactions';
        $this->urlSale = 'https://api.alliedwallet.com/merchants/' . $this->merchantId . '/saletransactions';
        
        $this->OAuthToken = 'AAEAAI_gMPeDnKEAufpMQAXH_BU9ZwHBkVwnKjy4QmHNYlGP9MALU_1rBadvsCyJ3nw1w710SMcdSx2QLqR5xXECZw55rPgzYjErSh_coU_IQxXn3EnvzzsZx17bo1ibwCvisqyaYIkdy44FuzlDxbwShvG-sMfMvALw2uHD7l1O9rKp3zwWa-t_jlObAK6vurD_CUsj3TdVY5FSjJmTNltqboMDQdQT8S8i-PpXPjVvGqauNe_J-Fywahz4Ap7SNFBejVnxoQresodm8KsqTlWDpjy4An8TRJ9hzqVeLAO9I-n0YWV1eT6Id7ZXiqwu6by-I4UyyEdaxn3jcEAHLTKuHJakAQAAAAEAAFDwtUQYiIS0MuL8736W-ycqOv9r6rXqfHQvk498LquIIRfQZ-ACO8SSKZHSEXS57BtWb_nJe-6xNydR7zrf7ssHKCw5BYKIvsD98uYxU6BgH2_TW4PiLeIEPw7KDTvgh-H5LdgMFhKs0qtBl42nAGazgJ1E0nBX3kZiMHXrdnxy9i4R6OxFDikjgAlZZcwIIASfa_-Wwn8APbHGrvX9ZTDpfvecj4Kj_U-0pVr8c_x3fEG7Tp5bixhBUXlYZVDYATyuwD6BqJfoCXPwY9EyQwghlzTXWoLpowuY9blB0OsUWmPVFO1hdR2x5L4SwI1i9TKcRFnHktVc5nHjqcfjyptNNBU9gLMxaGfwX9EfDfbGyIAdDBIk15hnNWjyE40AIrsFPDmGL0aQRZFUTXNivGCJJIU9MJCQTPRmp47YUPFPwuy5xBcmWb_9bzmUpJXCU7PJKC-JQm0QFCevs6rG2Dkzr0O_6c0CXZcZNFMsv57TnvPNbzoQv3LdbiLO6U4h2DL_-fCJsjf0auPsfyKLYXqp1OyOMZeC-or-GXNBX0hy';
    }

    public function pay($paymentData)
    {

        $curl = new Curl();
        
        $state = State::findFirst($paymentData['state']);
        
        $phone = '+' . $paymentData['phoneCode'];
        $numberLength = 11 - strlen($paymentData['phoneCode']);
        
        for ($i=0; $i < $numberLength; $i++) {
            $phone .= rand(0, 9);
        }

        $requestData = [
            'siteId'                => $this->siteId,
            'amount'                => $paymentData['total'],
            'currency'              => 'USD',
            'firstName'             => $paymentData['firstname'],
            'lastName'              => $paymentData['lastname'],
            'addressLine1'          => $paymentData['address'],
            'city'                  => $paymentData['city'],
            'state'                 => $state->iso,
            'countryId'             => $paymentData['country'],
            'postalCode'            => $paymentData['zipcode'],
            'email'                 => $paymentData['email'],
            'phone'                 => $phone,
            'cardNumber'            => $paymentData['cardNumber'],
            'nameOnCard'            => $paymentData['firstname'] . ' ' . $paymentData['lastname'],
            'expirationMonth'       => $paymentData['cardMonth'],
            'expirationYear'        => $paymentData['cardYear'],
            'cVVCode'               => $paymentData['cardCVV'],
            'iPAddress'             => $paymentData['ip'],
            'trackingId'            => $paymentData['orderId'],
            'isInitialForRecurring' => false,
        ];
        
        $headers = array('Authorization: Bearer ' . $this->OAuthToken);

        $response = $curl->request($this->urlSale, $requestData, 'json', $headers);
        $response = json_decode(urldecode($response['content']));
        
        if (isset($response->status) && $response->status == 'Successful') {
            
            return true;
            
        } else {
            
            if (isset($response->status)) {
                switch ($response->status) {
                    case 'Successful':
                        $this->status = true;
                        $this->statusMessage = $response->message;
                        break;
                    
                    case 'Error':
                        $this->error = $response->message;
                        break;
                    
                    case 'Declined':
                        $this->statusMessage = $response->message;
                        $this->error = $response->message . '(' . $response->reasonCode . ')';;
                        break;
                    
                    case 'Pending':
                        $this->status = 'pending';
                        $this->statusMessage = $response->message;
                        break;
                    
                    case 'Scrubbed':
                        $this->error = $response->message . '(' . $response->reasonCode . ')';
                        break;
                    
                    case 'Fraud':
                        $this->error = $response->message;
                        break;
                    
                    case 'Unconfirmed':
                        $this->error = $response->message;
                        break;
                }
            } else {
                $this->error = $response->message;
            }
            
            return $this->status;
        }

    }

}
