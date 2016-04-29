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
        ini_set('max_execution_time', 6000);
        ini_set('memory_limit', '2048M');
        $date = new DateTime();
        var_dump($date);
        $this->forward('FDResultBundle:Result:remove1Y');
        $date = new DateTime();
        var_dump($date);
        $this->forward('FDResultBundle:Result:get1N2');
        $date = new DateTime();
        var_dump($date);
        $this->forward('FDTeamBundle:Team:get');
        $date = new DateTime();
        var_dump($date);
        $this->forward('FDTeamBundle:Team:result');
        $date = new DateTime();
        var_dump($date);
        $this->forward('FDTeamBundle:Team:rank');
        $date = new DateTime();
        var_dump($date);
        $this->forward('FDOfferBundle:Offer:get1N2');
        $date = new DateTime();
        var_dump($date);
        return new Response("Hello World");
    }
}