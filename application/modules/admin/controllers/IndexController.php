<?php

namespace Admin\Controller;

use Phalcon\Mvc\Controller;
use Frontend\Model\Order;

class IndexController extends Controller
{
    private $identity;
    public $dashboard = ['title' => '', 'subtitle' => ''];
    public $breadcrumbs = [];

    public function initialize()
    {
        $this->identity = $this->di->get('auth')->getIdentity();

        if (!$this->identity) {
            $this->response->redirect('/admin/auth/login', true)->send();
        }
    }

    /**
     * Dashboard
     */
    public function indexAction()
    {
        $days = array();
        $oneDay = 60 * 60 * 24;
        $firstDay = time() - ($oneDay * 13);
        
        for ($i=0; $i < 14; $i++) {
            $days[] = $firstDay + ($i * $oneDay);
        }
        
        $daysNames = array();
        $products = array();
        $totals = array();
        
        for ($i=0; $i < 14; $i++) {
            $daysNames[$i] = date('d.m', $days[$i]);
            
            $dataProducts = Order::find([
                'columns' => 'COUNT(id) AS number, product',
                'conditions' => '(dateCreated BETWEEN ?1 AND ?2) AND product != ?3 AND status = ?4',
                'bind' => array(
                    1 => date('Y-m-d', $days[$i]),
                    2 => date('Y-m-d', $days[$i] + $oneDay),
                    3 => 0,
                    4 => 1
                ),
                'group' => 'product'
            ])->toArray();
            
            for ($x=1; $x < 10; $x++) {
                $products[$x][$i] = 0;
            }
            
            foreach ($dataProducts as $product) {
                $products[$product['product']][$i] = $product['number'];
            }
            
            $dataTotal = Order::findFirst([
                'columns' => 'SUM(total) AS amount',
                'conditions' => '(dateCreated BETWEEN ?1 AND ?2) AND product != ?3 AND status = ?4',
                'bind' => array(
                    1 => date('Y-m-d', $days[$i]),
                    2 => date('Y-m-d', $days[$i] + $oneDay),
                    3 => 0,
                    4 => 1
                )
            ]);
            
            $totals[$i] = (isset($dataTotal->amount)) ? $dataTotal->amount : 0;
        }
        
        $this->view->days = $daysNames;
        $this->view->products = $products;
        $this->view->totals = $totals;
        
        $this->view->setLayout('admin');
    }
    
    /**
     * Change locale
     */
    public function localeAction($locale = 'en')
    {
        $this->cookies->set('locale', $locale, time() + 30 * 86400);
    }
    
    protected function checkToken()
    {
        $key = '$PHALCON/CSRF/KEY$';
        $value = '$PHALCON/CSRF$';
    
        $tokenKey = $this->session->get($key);
        $token = $this->session->get($value);
    
        return $this->security->checkToken($tokenKey, $token);
    }
}
