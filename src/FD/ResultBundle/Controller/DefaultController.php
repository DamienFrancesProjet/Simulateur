<?php

namespace FD\ResultBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction()
    {
        return $this->render('FDResultBundle:Default:index.html.twig');
    }
}
