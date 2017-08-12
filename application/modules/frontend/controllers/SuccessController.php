<?php

namespace Frontend\Controller;

use Frontend\Controller\BaseController;
use Frontend\Model\Order;
use Swift_SmtpTransport;
use Swift_Mailer;
use Swift_Message;

class SuccessController extends BaseController
{

    public function initialize()
    {
        $this->translation();
        $this->view->setLayout('success');
    }

    public function indexAction()
    {
        $orderId = $this->session->get('orderId');

        $order = Order::findFirst($orderId);

        if (!empty($order->id)) {

            $order->status = 1;
            $order->update();
            
            $name = $order->firstname . ' ' . $order->lastname;
            
            $this->view->email = $order->email;
            
            $text = 'Thank you for your order.' . PHP_EOL . PHP_EOL;
            $text .= 'Your order confirmation number is: ' . $orderId . PHP_EOL;
            $text .= 'Order placed on ' . date('m/d/Y H:i') . PHP_EOL;
            $text .= 'As soon as your order is fulfilled and shipped, we’ll send you an e-mail notification with tracking details.' . PHP_EOL . PHP_EOL;
            $text .= 'If you have any questions, feel free to reach out to us at support@nostressfidget.com or ‎+17652318407' . PHP_EOL . PHP_EOL;
            $text .= 'Have a great day and thank you for choosing our product.' . PHP_EOL . PHP_EOL;
                        
            $transport = Swift_SmtpTransport::newInstance('smtp.mail.ru', 465, 'ssl')
                ->setUsername('support@nostressfidget.com')
                ->setPassword('Vitalij922');
                            
            $mailer = Swift_Mailer::newInstance($transport);
                        
            $message = Swift_Message::newInstance($transport)
                ->setSubject('Thank you for your order')
                ->setFrom(array('support@nostressfidget.com' => 'Customer Support Service'))
                ->setTo(array($order->email => $name))
                ->setBody($text);
                        
            $result = $mailer->send($message);

        } else {
            // $this->response->redirect('/');
        }

    }

}
