<?php

namespace Admin\Controller;

use Phalcon\Filter;
use DataTables\DataTable;
use Admin\Form\ProductForm;
use Frontend\Model\Product;

class ProductsController extends IndexController
{
    
    private $settings = [
        'payment'
    ];
    
    public function initialize()
    {
        $this->dashboard['title'] = $this->view->t->_('products');
        $this->breadcrumbs[] = [
            'name' => $this->view->t->_('products'),
            'href' => '/admin/products'
        ];
    }
    
    public function formAction($id = 0)
    {
        $this->dashboard['subtitle'] = $this->view->t->_('products');
        $this->view->dashboard = $this->dashboard;
        $this->view->breadcrumbs = $this->breadcrumbs;
        
        $form = new ProductForm(Product::findFirst($id));
        
        if ($this->request->isPost()) {
            
            if ($form->isValid($this->request->getPost()) && $this->checkToken()) {
            
                $product = Product::findFirst($id);
                $product->update($this->request->getPost());
            
            }
            
        }
        
        $this->view->payment = $this->settings->payment;
        $this->view->form = $form;
    }
    
    public function jsonAction()
    {
        $this->view->disable();
        
        if ($this->request->isPost() && $this->request->isAjax()) {
            $builder = $this->modelsManager->createBuilder()
                ->columns('id, name, paypal, price, shippingPrice')
                ->from('Frontend\Model\Product');

            $dataTables = new DataTable([], []);
            $dataTables->fromBuilder($builder)->sendResponse();
        } else {
            echo 'Invalid token';
        }
    }
    
}
