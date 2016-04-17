<?php

namespace FD\ResultBundle\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;


/**
 * Created by PhpStorm.
 * User: Damien
 * Date: 17/04/2016
 * Time: 21:23
 */
class ResultController extends Controller
{
    public function getAction()
    {
        $apiContent = file_get_contents("https://www.parionssport.fr/api/1n2/resultats?date=20160417");
        $resultInformation = json_decode($apiContent);
        var_dump($resultInformation);

        return new Response("Hello World");
    }
}