<?php

namespace FD\ResultBundle\Controller;


use DateTime;
use FD\ResultBundle\Entity\MarketResult;
use FD\ResultBundle\Entity\Result;
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
    public function get1N2Action()
    {

        ini_set('max_execution_time', 600);

        $dateStart = new DateTime();
        $dateStart->setDate(2015,04,18);

        $dateEnd = new DateTime();
        $dateEnd->setDate(2016,04,18);


        $em = $this->getDoctrine()->getManager('default');
        $offerRepository = $em->getRepository('FDResultBundle:Result');

        while($dateStart < $dateEnd)
        {
            $apiContent = file_get_contents("https://www.pointdevente.parionssport.fdj.fr/api/1n2/resultats?date=".$dateStart->format('Ymd'));
            $resultInformation = json_decode($apiContent);

            foreach($resultInformation as $resultItem)
            {
                //$resultQuery = $offerRepository->findBy(array('eventId' => $resultItem->eventId));
                //if(empty($resultQuery)) {
                    $result = new Result();
                    $result->setLabel($resultItem->label);
                    $result->setEventId($resultItem->eventId);

                    $result->setDate(\DateTime::createFromFormat('d/m/Y', substr($resultItem->end, 0, 10)));
                    $result->setCompetitionId($resultItem->competitionID);

                    $em->persist($result);

                    if($resultItem->marketRes[0]->marketType == '1/N/2');
                    {
                        $marketResult = new MarketResult();
                        $marketResult->setFDJNumber($resultItem->marketRes[0]->index);
                        $marketResult->setResult($result);
                        switch($resultItem->marketRes[0]->resultat)
                        {
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

                        $em->persist($marketResult);

                    }
                //}
            }
            $dateStart->modify('+1 day');
            var_dump($dateStart->format('Y-m-d'));
        }

        $em->flush();

        return new Response("Hello World");
    }
}