<?php

namespace FD\ResultBundle\Controller;


use DateTime;
use FD\ResultBundle\Entity\MarketResult;
use FD\ResultBundle\Entity\Result;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Constraints\Date;


/**
 * Created by PhpStorm.
 * User: Damien
 * Date: 17/04/2016
 * Time: 21:23
 */
class ResultController extends Controller
{
    public function remove1YAction()
    {
        ini_set('memory_limit', '2048M');
        ini_set('max_execution_time', 6000);

        $cptRemove = 0;

        $dateLimit = new DateTime();
        $dateLimit->modify('-1 year');

        $em = $this->getDoctrine()->getManager('default');
        $marketResultRepository = $em->getRepository('FDResultBundle:MarketResult');

        $marketResults = $marketResultRepository->FindByLtDate($dateLimit);

        foreach($marketResults as $marketResult)
        {
            $result = $marketResult->getResult();
            $em->remove($marketResult);
            $em->remove($result);
            $cptRemove = $cptRemove+2;

            if($cptRemove == 1000)
            {
                $em->flush();
                $cptRemove = 0;
            }
        }

        if($cptRemove > 0)
        {
            $em->flush();
        }

        var_dump("Result Remove1Y OK");

        return new Response("Hello World");
    }

    public function get1N2Action()
    {
        ini_set('memory_limit', '2048M');
        ini_set('max_execution_time', 6000);

        $cptPersist = 0;
        $dateStart = new DateTime();
        $dateStart->modify('-1 year');

        $dateEnd = new DateTime();


        $em = $this->getDoctrine()->getManager('default');
        $resultRepository = $em->getRepository('FDResultBundle:Result');

        while($dateStart < $dateEnd)
        {
            $apiContent = file_get_contents("https://www.pointdevente.parionssport.fdj.fr/api/1n2/resultats?date=".$dateStart->format('Ymd'));
            $results = json_decode($apiContent);
            if(!empty($results)) {
                foreach ($results as $resultItem) {
                    if (substr_count($resultItem->label, '-') == 1) {
                        $resultQuery = $resultRepository->findBy(array('eventId' => $resultItem->eventId));
                        if (empty($resultQuery)) {
                            $result = new Result();
                            $result->setLabel($resultItem->label);
                            $result->setEventId($resultItem->eventId);

                            $result->setDate(\DateTime::createFromFormat('d/m/Y', substr($resultItem->end, 0, 10)));
                            $result->setCompetitionId($resultItem->competitionID);

                            if ($resultItem->marketRes[0]->marketType == '1/N/2') ;
                            {
                                $marketResult = new MarketResult();
                                $marketResult->setFDJNumber($resultItem->marketRes[0]->index);
                                $marketResult->setResult($result);
                                switch ($resultItem->marketRes[0]->resultat) {
                                    case '1':
                                        $marketResult->setResultat('1');
                                        break;
                                    case '2':
                                        $marketResult->setResultat('N');
                                        break;
                                    case '3':
                                        $marketResult->setResultat('2');
                                        break;
                                }
                                $marketResult->setMarketType('1/N/2');

                                $em->persist($result);
                                $em->persist($marketResult);
                                $cptPersist = $cptPersist + 2;

                                if ($cptPersist == 1000) {
                                    $em->flush();
                                    $cptPersist = 0;
                                }

                            }
                        }
                    }
                }
            }
            $dateStart->modify('+1 day');
        }

        if($cptPersist > 0)
        {
            $em->flush();
        }
        var_dump("Result Get1N2 OK");
        return new Response("Hello World");
    }
}