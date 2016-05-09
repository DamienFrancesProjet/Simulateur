<?php

namespace FD\MainBundle\Controller;


use DateTime;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;


/**
 * Created by PhpStorm.
 * User: Damien
 * Date: 17/04/2016
 * Time: 21:23
 */
class MainController extends Controller
{
    public function mainAction()
    {
        return $this->render('FDMainBundle:Main:index.html.twig');
    }
}