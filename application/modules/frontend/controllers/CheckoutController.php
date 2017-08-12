<?php

namespace Frontend\Controller;

use Frontend\Controller\BaseController;
use Phalcon\Validation;
use Phalcon\Validation\Validator\PresenceOf;
use Phalcon\Validation\Validator\Digit;
use Phalcon\Validation\Validator\StringLength;
use Phalcon\Validation\Validator\Email;
use Phalcon\Validation\Validator\Callback;
use Phalcon\Validation\Validator\CreditCard;
use Cardinity\Client;
use Cardinity\Exception;
use Cardinity\Method\Payment;
use Phalcon\Logger;
use Phalcon\Logger\Adapter\File as FileAdapter;
use Frontend\Model\Product;
use Frontend\Model\Order;
use Frontend\Model\Country;
use Frontend\Model\State;

class CheckoutController extends BaseController
{

    private $errors = [];
    
    /*
     * Test: test_njm1njaaioprk9ng6oiciwqscdjsmh
     * Live: cf43asqenh3un5591vzetyyenhz1ev
     */
    private $consumerKey = 'cf43asqenh3un5591vzetyyenhz1ev';
    
    /*
     * Test: 3engk5nl8gnq2p6m7rz4ivmmynvbm1nkaygr5ryzj024obie0z
     * Live: lh6qjks3srge6rcgruidcxtfqln2sq0eo4nuzousmjzc9niqcj
     */
    private $consumerSecret = 'lh6qjks3srge6rcgruidcxtfqln2sq0eo4nuzousmjzc9niqcj';

    public function initialize()
    {
        $this->translation();
        $this->view->setLayout('checkout');
        $this->session->set('lang', $this->dispatcher->getParam('lang'));
    }

    public function indexAction()
    {
        $this->view->step = 1;

        $products = array();
        $productsData = Product::find();

        foreach ($productsData as $item) {
            $products[$item->id] = array(
                'name' => $item->name,
                'price'  => $item->price
            );
        }

        $this->view->products = $products;
        
        $europe = array('it', 'fr');
            
        if (in_array($this->dispatcher->getParam('lang'), $europe)) {
            $this->view->currency = '&euro;';
        } else {
            $this->view->currency = '$';
        }
    }

    public function step2Action()
    {
        $fields = array(
            'firstname'      => '',
            'lastname'       => '',
            'email'          => '',
            'card_number'    => '',
            'card_exp_month' => '',
            'card_exp_year'  => '',
            'card_cvv'       => '',
            'address'        => '',
            'zipcode'        => '',
            'city'           => '',
            'country'        => '',
            'state'          => ''
        );
        
        if ($this->request->isPost()) {
        
            $this->validate();
            
            foreach ($fields as $k => $v) {
                $fields[$k] = $this->request->getPost($k);
            }
        
        }
        
        $this->view->countries = Country::find();

        if (empty($this->view->states) && !empty($_SERVER['GEOIP_COUNTRY_CODE'])) {
            $country_code = $_SERVER['GEOIP_COUNTRY_CODE'];
            $country = Country::findFirstByIso($country_code);
            if (!empty($country->id)) {
                $this->view->client_country = $country->id;
                $this->view->states = State::findByCountryId($country->id);
            }
        }
        
        if (!empty($fields['country'])) {
            $this->view->states = State::findByCountryId($fields['country']);
        }

        $product = Product::findFirst($this->session->get('product'));

        $this->view->order = $order;
        $this->view->product = $product;
        $this->view->products = Product::find();

        $this->view->step = 2;
        $this->view->fields = $fields;
    }

    public function step3Action()
    {
        $orderId = $this->session->get('orderId');
        
        if (!$orderId) {
            $this->response->redirect('/checkout/step2');
        }

        $order = Order::findFirst($orderId);
        
        $tokenKey = $this->session->get('$PHALCON/CSRF/KEY$');
        $tokenValue = $this->request->getPost($tokenKey);
        
        $product = Product::findFirst($order->product);

        $this->view->order = $order;
        $this->view->product = $product;
        $this->view->products = Product::find();
        
        /*
        if ($this->request->isPost() && $this->validate() && $this->request->getPost('card_number') && $this->security->checkToken($tokenKey, $tokenValue)) {
            try {
            
                $state = '';
                $ip = $this->request->getClientAddress();
                $cardType = $this->getCardType($this->request->getPost('card_number'));
                
                if ($cardType) {
                    
                    $total = $product->price + $product->shippingPrice;
                    
                    $country = Country::findFirst($order->country);
                    
                    $data = array(
                        'orderId'    => $orderId,
                        'firstname'  => $order->firstname,
                        'lastname'   => $order->lastname,
                        'address'    => $order->address,
                        'city'       => $order->city,
                        'state'      => $order->state,
                        'country'    => $country->iso,
                        'phoneCode'  => $country->phoneCode,
                        'zipcode'    => $order->zipcode,
                        'email'      => $order->email,
                        'cardType'   => $cardType,
                        'cardNumber' => $this->request->getPost('card_number'),
                        'cardMonth'  => $this->request->getPost('card_exp_month'),
                        'cardYear'   => $this->request->getPost('card_exp_year'),
                        'cardCVV'    => $this->request->getPost('card_cvv'),
                        'ip'         => $ip,
                        'total'      => $total,
                    );
                    
                    $payment = new \App\Payment\AlliedWallet();
                    
                    if ($payment->pay($data)) {
                        $order->paymentError = '';
                        $this->response->redirect('/success');
                    } else {
                        $order->paymentError = $payment->error;
                        $this->flashSession->error($payment->error);
                    }
                } else {
                    $this->flashSession->error('Card type not supported');
                }
            
            } catch (\Exception $e) {
                
                $this->flashSession->error($e->getMessage());
                
            }
        }
        */

        if ($this->request->isPost()) {
            // $apiKey = '02dc923397252afc5218531c085284f4'; // TEST
            $apiKey = 'f6f1cbf4df34107972813d20be42f0e7'; // LIVE
            $request = new \Paymill\Request($apiKey);
            $transaction = new \Paymill\Models\Request\Transaction();
            $transaction->setAmount($this->request->getPost('amount'))
                        ->setCurrency($this->request->getPost('currency'))
                        ->setToken($this->request->getPost('token'))
                        ->setDescription($this->request->getPost('description'));

            try {
                $response = $request->create($transaction);
                $this->response->redirect('/success');
            } catch(\Paymill\Services\PaymillException $e){
                // echo('An error occured while processing the transaction: ');
                // echo($e->getErrorMessage());
                $this->flashSession->error($e->getErrorMessage());
            }
        }

        $this->view->step = 3;
    }

    public function validateAction()
    {
        $this->view->disable();
        $response = new \Phalcon\Http\Response();

        $step = $this->request->getPost('step', 'int');

        switch($step) {
            case 1:
                $result = $this->validateStep1();
                break;
           case 2:
                $result = $this->validateStep2();
                break;
           case 3:
                $result = $this->validateStep3();
                break;
        }


        $response->setHeader('Cache-Control', 'private, max-age=0, must-revalidate');

        if ($result === true) {
            $response->setContentType('application/json', 'UTF-8');
            $response->setContent(json_encode(['status' => 'ok']));
        } else {
            $result['status'] = 'error';
            $response->setContentType('application/json', 'UTF-8');
            $response->setContent(json_encode($result));
        }

        $this->response->setCache(0);
        $response->setHeader('Pragma', 'no-cache');
        $response->sendHeaders();
        $response->send();
    }

    private function validateStep1()
    {
        $validation = new Validation();

        $validation->add(
            ['product'],
            new PresenceOf([
                'message' => [
                    'product' => 'Please, select product',
                ]
            ])
        );

        $validation->add(
            ['product'],
            new Digit([
                'message' => [
                    'product' => 'Data type is invalid. Please, select product',
                ]
            ])
        );

        $validation->add(
            'product',
            new Callback(
                [
                    'callback' => function($data) {
                        if ($data['product'] >= 1 && $data['product'] <= 9) {
                            return true;
                        } else {
                            return false;
                        }
                    },
                    'message' => 'Please, select product'
                ]
            )
        );

        $messages = $validation->validate($_POST);

        $errors = array();
        $errors['errors'] = '';

        if (count($messages)) {
            foreach ($messages as $message) {
                $field = $message->getField();
                $errors['errors'] .= '<li>' . $message->getMessage() . '</li>';
            }
            return $errors;
        } else {
            $this->session->set('product', $this->request->getPost('product', ['int']));

            return true;
        }
    }

    private function validateStep2()
    {
        $validation = new Validation();

        $validation->add(
            ['firstname', 'lastname', 'address', 'zipcode', 'country', 'state', 'city', 'email'],
            new PresenceOf([
                'message' => [
                    'firstname' => 'Firstname is required',
                    'lastname'  => 'Lastname is required',
                    'address'   => 'Address is required',
                    'zipcode'   => 'Zipcode is required',
                    'country'   => 'Country is required',
                    'state'     => 'State is required',
                    'city'      => 'City is required',
                    'email'     => 'Email is required',
                ]
            ])
        );

        $validation->add(
            'email',
            new Email(
                [
                    'message' => 'The e-mail is not valid',
                ]
            )
        );

        $messages = $validation->validate($_POST);

        $errors = array();
        $errors['errors'] = '';

        if (count($messages)) {
            foreach ($messages as $message) {
                $field = $message->getField();
                $errors['errors'] .= '<li>' . $message->getMessage() . '</li>';
            }

            return $errors;
        } else {

            $order = new Order();
            $product = Product::findFirst($this->session->get('product'));

            $order->firstname = $this->request->getPost('firstname', ['string', 'trim', 'striptags']);
            $order->lastname = $this->request->getPost('lastname', ['string', 'trim', 'striptags']);
            $order->address = $this->request->getPost('address', ['string', 'trim', 'striptags']);
            $order->country = $this->request->getPost('country', ['int']);
            $order->zipcode = $this->request->getPost('zipcode', ['string', 'trim', 'striptags']);
            $order->state = $this->request->getPost('state', ['int']);
            $order->city = $this->request->getPost('city', ['string', 'trim', 'striptags']);
            // $order->phone = $this->request->getPost('phone', ['string', 'trim', 'striptags']);
            $order->phone = '-----';
            $order->email = $this->request->getPost('email', 'email');
            $order->product = $this->session->get('product');
            $order->ip = $this->request->getClientAddress();
            $order->total = $product->price + $product->shippingPrice;
            $order->status = 0;

            if ($order->save()) {
                $this->session->set('orderId', $order->id);
                return true;
            } else {
                $messages = $order->getMessages();

                foreach ($messages as $message) {
                    $errors['errors'] .= '<li>' . $message . '</li>';
                }

                $errors['errors'] .= '<li>Error. Please, try again.</li>';
                return $errors;
            }
        }
    }
    
    public function validate()
    {
        
        $_POST['card_number'] = str_replace(' ', '', $_POST['card_number']);
        
        $validation = new Validation();

        $validation->add(
            ['product', 'firstname', 'lastname', 'address', 'zipcode', 'country', 'state', 'city', 'email', 'card_number', 'card_exp_month', 'card_exp_year', 'card_cvv'],
            new PresenceOf([
                'message' => [
                    'product'        => 'Please, select product',
                    'firstname'      => 'Firstname is required',
                    'lastname'       => 'Lastname is required',
                    'address'        => 'Address is required',
                    'zipcode'        => 'Zipcode is required',
                    'country'        => 'Country is required',
                    'state'          => 'State is required',
                    'city'           => 'City is required',
                    'email'          => 'Email is required',
                    'card_number'    => 'Credit card number  is required',
                    'card_exp_month' => 'Credin card expire month is required',
                    'card_exp_year'  => 'Credin card expire year is required',
                    'card_cvv'       => 'Credin card security code  is required',
                ]
            ])
        );

        $validation->add(
            ['product'],
            new Digit([
                'message' => [
                    'product' => 'Data type is invalid. Please, select product',
                ]
            ])
        );

        $validation->add(
            'product',
            new Callback(
                [
                    'callback' => function($data) {
                        if ($data['product'] >= 1 && $data['product'] <= 9) {
                            return true;
                        } else {
                            return false;
                        }
                    },
                    'message' => 'Please, select product'
                ]
            )
        );
        
        $validation->add(
            'card_exp_month',
            new Callback(
                [
                    'callback' => function($data) {
                        if ((int)$data['card_exp_month'] >= 1 && (int)$data['card_exp_month'] <= 12) {
                            return true;
                        } else {
                            return false;
                        }
                    },
                    'message' => 'Enter month between 01 and 12'
                ]
            )
        );
        
        $validation->add(
            'card_exp_month',
            new Callback(
                [
                    'callback' => function($data) {
                        if ((int)$data['card_exp_year'] >= date('Y')) {
                            return true;
                        } else {
                            return false;
                        }
                    },
                    'message' => 'Enter year from ' . date('Y')
                ]
            )
        );
        
        $validation->add(
            'email',
            new Email(
                [
                    'message' => 'The e-mail is not valid',
                ]
            )
        );
        
        $validation->add(
            ['card_number', 'card_exp_month', 'card_exp_year', 'card_cvv'],
            new Digit([
                'message' => [
                    'card_number'    => 'Credit card number must be numeric',
                    'card_exp_month' => 'Credit card expire month must be numeric',
                    'card_exp_year'  => 'Credit card expire year must be numeric',
                    'card_cvv'       => 'Credit card security code must be numeric',
                ]
            ])
        );

        $validation->add(
            ['card_number', 'card_exp_month', 'card_exp_year', 'card_cvv'],
            new StringLength([
                'max' => [
                    'card_number'    => 19,
                    'card_exp_month' => 2,
                    'card_exp_year'  => 4,
                    'card_cvv'       => 350,
                ],
                'min' => [
                    'card_number'    => 15,
                    'card_exp_month' => 2,
                    'card_exp_year'  => 4,
                    'card_cvv'       => 3,
                ],
                'messageMaximum' => [
                    'card_number'    => 'Credit card number can\'t be longer than 19 chars',
                    'card_exp_month' => 'Credit card expire month can\'t be longer than 2 chars',
                    'card_exp_year'  => 'Credit card expire year can\'t be longer than 4 chars',
                    'card_cvv'       => 'Credit card security code can\'t be longer than 3 chars',
                ],
                'messageMinimum' => [
                    'card_number'    => 'Credit card number must be at least 16 chars',
                    'card_exp_month' => 'Credit card expire month must be at least 2 chars',
                    'card_exp_year'  => 'Credit card expire year must be at least 4 chars',
                    'card_cvv'       => 'Credit card security code must be at least 3 chars',
                ]
            ])
        );

        $validation->add(
            'card_number',
            new CreditCard(['message' => 'Credit card number is not valid'])
        );
        
        $messages = $validation->validate($_POST);

        if (count($messages)) {
            $errors = array();
            $errors['errors'] = '';
            $errors['status'] = 'error';
            
            foreach ($messages as $message) {
                $field = $message->getField();
                $this->flashSession->error($message->getMessage());
            }
        } else {
            $order = new Order();
            $product = Product::findFirst($this->request->getPost('product', ['int']));
            
            $country = Country::findFirst($this->request->getPost('country', ['int']));

            $order->firstname = $this->request->getPost('firstname', ['string', 'trim', 'striptags']);
            $order->lastname = $this->request->getPost('lastname', ['string', 'trim', 'striptags']);
            $order->address = $this->request->getPost('address', ['string', 'trim', 'striptags']);
            $order->country = $this->request->getPost('country', ['int']);
            $order->zipcode = $this->request->getPost('zipcode', ['string', 'trim', 'striptags']);
            $order->state = $this->request->getPost('state', ['int']);
            $order->city = $this->request->getPost('city', ['string', 'trim', 'striptags']);
            // $order->phone = $this->request->getPost('phone', ['string', 'trim', 'striptags']);
            $order->phone = '-----';
            $order->email = $this->request->getPost('email', 'email');
            $order->product = $this->request->getPost('product', ['int']);
            $order->ip = $this->request->getClientAddress();
            $order->total = $product->price + $product->shippingPrice;
            $order->status = 0;

            if ($order->save()) {
                $this->session->set('orderId', $order->id);
                
                $client = Client::create([
                    'consumerKey'    => $this->consumerKey,
                    'consumerSecret' => $this->consumerSecret,
                ]);
                    
                $method = new Payment\Create([
                    'amount' => $product->price + $product->shippingPrice,
                    'currency' => $this->request->getPost('currency', ['string', 'trim', 'striptags']),
                    'settle' => true,
                    'description' => 'Hand Spinner', // Hand Spinner
                    'order_id' => $order->id,
                    'country' => $country->iso,
                    'payment_method' => Payment\Create::CARD,
                    'payment_instrument' => [
                        'pan' => $this->request->getPost('card_number', ['string', 'trim', 'striptags']),
                        'exp_year' => (int)$this->request->getPost('card_exp_year', ['int']),
                        'exp_month' => (int)$this->request->getPost('card_exp_month', ['int']),
                        'cvc' => $this->request->getPost('card_cvv', ['int']),
                        'holder' => $this->request->getPost('firstname', ['string', 'trim', 'striptags']) . ' ' . $this->request->getPost('lastname', ['string', 'trim', 'striptags'])
                    ],
                ]);
                
                try {
                    $payment = $client->call($method);
                    $order->paymentId = $payment->getId();
                    $order->update();
                    
                    if ($payment->getStatus() == 'pending' && $payment->getAuthorizationInformation()) {
                        $this->session->set('paymentData', $payment->getAuthorizationInformation()->getData());
                        $this->session->set('paymentId', $payment->getId());
                        $this->view->url = $payment->getAuthorizationInformation()->getUrl();
                        $this->view->PaReq = $payment->getAuthorizationInformation()->getData();
                        $this->view->MD = $order->id;
                        $this->view->setLayout('checkout-3d');
                    }
                    
                    if ($payment->getStatus() == 'approved') {
                        $order->status = 1;
                        $order->update();
                        $this->response->redirect('/success');
                    }
                } catch (Exception\Request $e) {
                    $order->paymentError = $e->getErrorsAsString();
                    $order->save();
                    $this->flashSession->error($e->getErrorsAsString());
                }
            } else {
                $messages = $order->getMessages();

                foreach ($messages as $message) {
                    $this->flashSession->error($message);
                }
            }
        }
    }
    
    public function responseAction()
    {
    
        $paymentId = $this->session->get('paymentId');
        $PaRes = $this->request->getPost('PaRes');
        $orderId = $this->request->getPost('MD');
        $this->view->setLayout('order-3d-response');
        
        $order = Order::findFirst($orderId);
        
        $client = Client::create([
            'consumerKey'    => $this->consumerKey,
            'consumerSecret' => $this->consumerSecret,
        ]);
        
        $method = new Payment\Finalize($paymentId, $PaRes);
        
        try {
            $payment = $client->call($method);
                    
            if ($payment->getStatus() == 'approved') {
                $this->response->redirect('/success');
            }
        } catch (Exception\Request $e) {
            $order->paymentError = $e->getErrorsAsString();
            $order->update();
            $this->flashSession->error($e->getErrorsAsString());
            $lang = $this->session->get('lang');
            if (empty($lang)) {
                $this->response->redirect('/checkout');
            } else {
                $this->response->redirect('/' . $lang . '/checkout');
            }
        }
    
    }
    
    public function getCardType($cardNumber)
    {
        $cardType = false;
        
        $cardNumber = str_replace(' ', '', $cardNumber);
            
        $card2Digits = (int)substr($cardNumber, 0, 2);
        $card3Digits = (int)substr($cardNumber, 0, 3);
        $card4Digits = (int)substr($cardNumber, 0, 4);
        $card6Digits = (int)substr($cardNumber, 0, 6);
            
        if ($card2Digits >= 51 && $card2Digits <= 55) {
            $cardType = 'MasterCard';
        } elseif ($card2Digits >= 40 && $card2Digits <= 49) {
            $cardType = 'Visa';
        } elseif ($card2Digits == 34 || $card2Digits == 37) {
            $cardType = 'AMEX';
        } elseif ($card2Digits == 65 || $card3Digits >= 644 && $card3Digits <= 649 || $card4Digits == 6011 || $card6Digits >= 622126 && $card6Digits <= 622925) {
            $cardType = 'Discover';
        }
        
        return $cardType;
    }
    
    public function testAction()
    {
        $this->view->setLayout('test-checkout');
        
        $orderId = $this->session->get('orderId');
        
        $orderId = 2;
        
        if (!$orderId) {
            $this->response->redirect('/checkout/step2');
        }

        $order = Order::findFirst($orderId);
        
        $product = Product::findFirst($order->product);

        $this->view->order = $order;
        $this->view->product = $product;
        $this->view->products = Product::find();

        if ($this->request->isPost()) {
            // $apiKey = '02dc923397252afc5218531c085284f4'; // TEST
            $apiKey = 'f6f1cbf4df34107972813d20be42f0e7'; // LIVE
            $request = new \Paymill\Request($apiKey);
            $transaction = new \Paymill\Models\Request\Transaction();
            $transaction->setAmount($this->request->getPost('amount'))
                        ->setCurrency($this->request->getPost('currency'))
                        ->setToken($this->request->getPost('token'))
                        ->setDescription($this->request->getPost('description'));

            try {
                $response = $request->create($transaction);
                $this->response->redirect('/success');
            } catch(\Paymill\Services\PaymillException $e){
                // echo('An error occured while processing the transaction: ');
                // echo($e->getErrorMessage());
                $this->flashSession->error($e->getErrorMessage());
            }
        }

        $this->view->step = 3;
    }
    
    public function logAction()
    {
        $logger = new FileAdapter(APPLICATION_PATH . '/logs/paymentErrors.log');
        $logger->log($this->request->getPost('message') . '. Order ID: ' . $this->request->getPost('id'));
    }

}
