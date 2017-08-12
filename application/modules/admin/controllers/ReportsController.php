<?php

namespace Admin\Controller;

use Phalcon\Mvc\Controller;
use Frontend\Model\Order;
use Frontend\Model\Product;
use PHPExcel;
use PHPExcel_IOFactory;

/*include '../vendor/os/php-excel/PHPExcel/PHPExcel.php';
include 'PHPExcel/Writer/Excel2007.php';*/

class ReportsController extends Controller
{
    
    const ONEDAY = 60 * 60 * 24;
    
    public function initialize()
    {
        
    }
    
    public function xlsAction()
    {
        $this->view->disable();
        
        $dateStart = ($this->request->getQuery('dateStart')) ? $this->request->getQuery('dateStart') : $this->monthStartDate();
        $dateFinal = ($this->request->getQuery('dateFinal')) ? $this->request->getQuery('dateFinal') : $this->monthFinalDate();
        
        if ($this->request->getQuery('dateFinal')) {
            $dateFinal = date('Y-m-d', strtotime($dateFinal) + self::ONEDAY);
        }
        
        $orders = Order::find([
            'conditions' => '(dateCreated BETWEEN ?1 AND ?2) AND status != ?3',
            'bind' => [
                1 => $dateStart,
                2 => date('Y-m-d', strtotime($dateFinal) + self::ONEDAY),
                3 => 0
            ]
        ]);
        
        $xls = new PHPExcel();
        
        $xls->getProperties()
            ->setCreator('Vitaliy Kamelin')
            ->setLastModifiedBy('Vitaliy Kamelin')
            ->setTitle('Sales')
            ->setSubject('Sales Report')
            ->setDescription('Sales report for dates from ' . $dateStart . ' to ' . $dateFinal)
            ->setKeywords('')
            ->setCategory('Sales');
                             
        $xls->setActiveSheetIndex(0)
            ->setCellValue('A1', 'Name')
            ->setCellValue('B1', 'Shipping Address')
            ->setCellValue('C1', 'Pack')
            ->setCellValue('D1', 'Total')
            ->setCellValue('E1', 'Date')
            ->setCellValue('F1', 'Shipping');
        
        $i = 2;
        
        $products = array();
        $productsData = Product::find();
        
        foreach ($productsData as $item) {
            $products[$item->id] = $item->paypal;
        }
        
        foreach ($orders as $order) {
            if (($order->country == 0 || $order->country == 1) && $order->state == 0) {
                $address = 'No address, Paypal order';
            } else {
                $address = $order->address . ', ' . $order->city . ', ' . $order->State->name . ', ' . $order->Country->name . ', ' . $order->zipcode;
            }
            
            $shipping = '-';
            
            switch ($order->status) {
                case 1:
                    $shipping = '-';
                    break;
                case 2:
                    $shipping = 'Shipped';
                    break;
                case 3:
                    $shipping = 'Delivered';
                    break;
            }
            
            $xls->setActiveSheetIndex(0)
                ->setCellValue('A' . $i, $order->firstname . ' ' . $order->lastname)
                ->setCellValue('B' . $i, $address)
                ->setCellValue('C' . $i, ($order->product != 0) ? $products[$order->product] : '')
                ->setCellValue('D' . $i, '$' . $order->total)
                ->setCellValue('E' . $i, $order->dateCreated)
                ->setCellValue('F' . $i, $shipping);
                
            $i++;
        }
                    
        $xls->getActiveSheet()->setTitle('Report ' . $dateStart . ' - ' . $dateFinal);
        
        $this->response->setHeader('Content-Type', 'application/vnd.ms-excel');
        $this->response->setHeader('Content-Disposition', 'attachment;filename="Report from ' . $dateStart . ' to ' . $dateFinal . '.xls"');
        $this->response->setHeader('Cache-Control', 'max-age=0');
        $this->response->setHeader('Expires', 'Mon, 26 Jul 1997 05:00:00 GMT');
        $this->response->setHeader('Last-Modified', gmdate('D, d M Y H:i:s') . ' GMT');
        
        $writer = PHPExcel_IOFactory::createWriter($xls, 'Excel5');
        $writer->save('php://output');
        $this->response->send();
        exit();
        
    }
    
    private function monthStartDate()
    {
        return date('Y-m-01');
    }
    
    private function monthFinalDate()
    {
        
        return date('Y-m-t');
    }
    
}
