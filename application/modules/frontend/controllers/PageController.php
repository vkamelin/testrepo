<?php

namespace Frontend\Controller;

use Frontend\Controller\BaseController;

class PageController extends BaseController
{

    public function indexAction($slug)
    {
        var_dump($slug); exit();
    }
}
