<?php

namespace Admin\Controller;

use Phalcon\Filter;
use DataTables\DataTable;
use Frontend\Model\Order;
use Admin\Form\OrderForm;
use Swift_SmtpTransport;
use Swift_Mailer;
use Swift_Message;
use Spipu\Html2Pdf\Html2Pdf;
use Spipu\Html2Pdf\Exception\Html2PdfException;
use Spipu\Html2Pdf\Exception\ExceptionFormatter;

class OrdersController extends IndexController
{
    
    public function initialize()
    {
        $this->dashboard['title'] = $this->view->t->_('orders');
        $this->breadcrumbs[] = [
            'name' => $this->view->t->_('orders'),
            'href' => '/admin/orders'
        ];
    }
    
    public function indexAction()
    {
        $this->dashboard['subtitle'] = $this->view->t->_('all_orders');
        $this->view->dashboard = $this->dashboard;
        $this->view->breadcrumbs = $this->breadcrumbs;
    }
    
    public function formAction($id = 0)
    {
        $this->dashboard['subtitle'] = $this->view->t->_('order_edit');
        
        $this->view->dashboard = $this->dashboard;
        $this->view->breadcrumbs = $this->breadcrumbs;
        
        if ($id == 0) {
            $this->response->setStatusCode(404, 'Not Found');
        } else {
            $form = new OrderForm(Order::findFirst($id));
        }

        if ($this->request->isPost()) {
            
            try {
                if ($form->isValid($this->request->getPost()) && $this->checkToken()) {
                    
                    $order = Order::findFirst($id);
                    
                    if ($order && $order->trackNumber != $this->request->getPost('trackNumber')) {
                        $name = $order->firstname . ' ' . $order->lastname;
                        $date = strtotime($this->request->getPost('trackNumberDate'));
                        
                        $text = 'Dear client, this is an email notification to inform you that your order  ID ' . $id . ' has shipped on ' . date('m/d/Y', $date) . ' at ' . date('H:i', $date) . '.';
                        $text .= 'Your delivery confirmation number is ' . $this->request->getPost('trackNumber') . ' and you eventually will be able to track your order using this number.' . PHP_EOL . PHP_EOL;
                        
                        $text .= 'http://www.track-trace.com/post'. PHP_EOL . PHP_EOL;
                        
                        $text .= 'We hope we meet your expectations. If there is anything else we can do for you, just let us know.';
                        
                        $transport = Swift_SmtpTransport::newInstance('smtp.mail.ru', 465, 'ssl')
                            ->setUsername('support@nostressfidget.com')
                            ->setPassword('Vitalij922');
                            
                        $mailer = Swift_Mailer::newInstance($transport);
                        
                        $message = Swift_Message::newInstance($transport)
                            ->setSubject('Your tracking number')
                            ->setFrom(array('support@nostressfidget.com' => 'Customer Support Service'))
                            ->setTo(array($this->request->getPost('email') => $name))
                            ->setBody($text);
                        
                        $result = $mailer->send($message);
                        
                        $order->status = 2;
                        
                        $orderData = $this->request->isPost();
                        $orderData['status'] = 2;
                        
                        $order->update($this->request->isPost());
                    }
                    
                    $order->update($this->request->getPost());
                    
                    
                } else {
                    $messages = $form->getMessages();
    
                    foreach ($messages as $message) {
                        echo $message;
                        $this->flashSession->error($message . ' ' . $this->security->getSessionToken());
                    }
                }
            } catch (\Exception $e) {
                $this->flashSession->error($e->getMessage());
            }
            
        }
        
        $order = Order::findFirst($id);
        
        $this->view->order = Order::findFirst($id);
        $this->view->product = $order->getProduct();

        $this->view->form = $form;
    }
    
    public function invoiceAction($id = 0)
    {
        $order = Order::findFirst($id);
        
        $this->view->order = $order;
        $this->view->setRenderLevel(\Phalcon\Mvc\View::LEVEL_ACTION_VIEW);
        $this->view->start();
        $this->view->render('orders', 'invoice');
        $this->view->finish();
        
        $content = $this->view->getContent();
        $this->view->disable();
        
        header('Content-type:application/pdf');
        header('Content-Disposition:attachment;filename=INVOICE-' . $id . '.pdf');

        try {
            $html2pdf = new Html2Pdf('P', 'A4', 'en', false, 'UTF-8');
            $html2pdf->setDefaultFont('Arial');
            $html2pdf->writeHTML($content);
            $html2pdf->output('exemple00.pdf');
        } catch (Html2PdfException $e) {
            $formatter = new ExceptionFormatter($e);
            echo $formatter->getHtmlMessage();
        }
    }
    
    public function jsonAction()
    {
        $this->view->disable();
        
        if (isset($_POST['search']['value'])) {
            switch ($_POST['search']['value']) {
                case 'created':
                    $_POST['search']['value'] = 0;
                    $statusSearch = true;
                    break;
                case 'paid':
                    $_POST['search']['value'] = 1;
                    $statusSearch = true;
                    break;
                case 'sended':
                    $_POST['search']['value'] = 2;
                    $statusSearch = true;
                    break;
                case 'delivered':
                    $_POST['search']['value'] = 3;
                    $statusSearch = true;
                    break;
                default:
                    $statusSearch = false;
            }
            
            if ($statusSearch) {
                $columnsNumber = count($_POST['columns']);
                
                for ($i = 0; $i < $columnsNumber; $i++) {
                    if ($i != 6) {
                        $_POST['columns'][$i]['searchable'] = false;
                    }
                }
            }
        }
        
        if ($this->request->isPost() && $this->request->isAjax()) {
            $builder = $this->modelsManager->createBuilder()
                ->columns('id, firstname, lastname, email, dateCreated, dateModified, status, paymentError')
                ->from('Frontend\Model\Order');

            $dataTables = new DataTable([], []);
            $dataTables->fromBuilder($builder)->sendResponse();
        } else {
            echo 'Invalid token';
        }
    }
    
}
