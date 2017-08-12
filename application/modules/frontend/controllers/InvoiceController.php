<?php

namespace Frontend\Controller;

use Phalcon\Mvc\View;
use Frontend\Controller\BaseController;
use Frontend\Model\Order;
use Spipu\Html2Pdf\Html2Pdf;
use Spipu\Html2Pdf\Exception\Html2PdfException;
use Spipu\Html2Pdf\Exception\ExceptionFormatter;

class InvoiceController extends BaseController
{

    private $basePath = '/home/admin/web/nostressfidget.com/public_shtml';
    
    public function initialize()
    {
        $this->view->setRenderLevel(
            View::LEVEL_ACTION_VIEW
        );
    }

    public function indexAction()
    {
        
        //$this->view->disable(); // var_dump(__DIR__); exit();
        
        $order = Order::findFirst(20); // var_dump($order); exit();
        
        var_dump($this->view->render('invoice', 'index'));

        /*
        try {
            $content = '<h1>Hello</h1><p>world!</p>';
            $html2pdf = new Html2Pdf('P', 'A4', 'en', false, 'UTF-8');
            $html2pdf->setDefaultFont('Arial');
            $html2pdf->writeHTML($content);
            $html2pdf->output('exemple00.pdf');
        } catch (Html2PdfException $e) {
            $formatter = new ExceptionFormatter($e);
            echo $formatter->getHtmlMessage();
        }
        */

    }

}
