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

class OrderController extends BaseController
{

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
        $this->view->setLayout('order');
        $this->session->set('lang', $this->dispatcher->getParam('lang'));
    }

    public function indexAction()
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

        $products = array();
        $productsData = Product::find();
        
        foreach ($productsData as $item) {
            $products[$item->id] = array(
                'paypal'  => $item->paypal,
                'price' => $item->price
            );
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
            
        $europe = array('it', 'fr');
            
        if (in_array($this->dispatcher->getParam('lang'), $europe)) {
            $this->view->currency = '&euro;';
        } else {
            $this->view->currency = '$';
        }
        
        if ($this->request->getPost('product', ['int'])) {
            $product = Product::findFirst($this->request->getPost('product', ['int']));
        } else {
            $product = Product::findFirst(12);
        }

        $this->view->products = $products;
        $this->view->product = $product;
        
        $this->view->fields = $fields;
    }
    
    public function paypalAction()
    {
        $_POST['card_number'] = str_replace(' ', '', $_POST['card_number']);
        
        $validation = new Validation();

        $validation->add(
            ['product', 'firstname', 'lastname', 'email'],
            new PresenceOf([
                'message' => [
                    'product'        => 'Please, select product',
                    'firstname'      => 'Firstname is required',
                    'lastname'       => 'Lastname is required',
                    'email'          => 'Email is required',
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
                        if ($data['product'] >= 10 && $data['product'] <= 14) {
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
            'email',
            new Email(
                [
                    'message' => 'The e-mail is not valid',
                ]
            )
        );
        
        $lang = $this->session->get('lang');
        
        $messages = $validation->validate($_POST);

        if (count($messages)) {
            $errors = array();
            $errors['errors'] = '';
            $errors['status'] = 'error';
            
            foreach ($messages as $message) {
                $field = $message->getField();
                $this->flashSession->error($message->getMessage());
            }
            
            if (empty($lang)) {
                $this->response->redirect('/order');
            } else {
                $this->response->redirect('/' . $lang . '/order');
            }
        } else {
            $order = new Order();
            $product = Product::findFirst($this->request->getPost('product', ['int']));
            
            $country = Country::findFirst($this->request->getPost('country', ['int']));

            $order->firstname = $this->request->getPost('firstname', ['string', 'trim', 'striptags']);
            $order->lastname = $this->request->getPost('lastname', ['string', 'trim', 'striptags']);
            $order->address = '---';
            $order->country = '0';
            $order->zipcode = '0';
            $order->state = '0';
            $order->city = '---';
            // $order->phone = $this->request->getPost('phone', ['string', 'trim', 'striptags']);
            $order->phone = '-----';
            $order->email = $this->request->getPost('email', 'email');
            $order->product = $this->request->getPost('product', ['int']);
            $order->ip = $this->request->getClientAddress();
            $order->total = $product->price + $product->shippingPrice;
            $order->status = 0;

            if ($order->save()) {
                $this->session->set('orderId', $order->id);
                $this->view->product = $product;
                $this->view->setLayout('order-paypal');
            } else {
                $messages = $order->getMessages();

                foreach ($messages as $message) {
                    $this->flashSession->error($message);
                }
                
                if (empty($lang)) {
                    $this->response->redirect('/order');
                } else {
                    $this->response->redirect('/' . $lang . '/order');
                }
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
                        if ($data['product'] >= 10 && $data['product'] <= 14) {
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
                    'currency' => 'USD',
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
                        $this->view->setLayout('order-3d');
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
                } catch (Exception\Declined $exception) {
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
        
        $method = new Payment\Get($paymentId);
        $payment = $client->call($method);
        
        if ($payment->getStatus() != 'declined') {
        
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
                    $this->response->redirect('/order');
                } else {
                    $this->response->redirect('/' . $lang . '/order');
                }
            }
        
        } else {
            
            $order->paymentError = $payment->getError();
            $order->save();
            $this->flashSession->error($payment->getError());
            
            if (empty($lang)) {
                $this->response->redirect('/order');
            } else {
                $this->response->redirect('/' . $lang . '/order');
            }
            
        }
    
    }

}
