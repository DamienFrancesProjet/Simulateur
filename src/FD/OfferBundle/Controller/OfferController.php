<?php
/**
 * Created by PhpStorm.
 * User: Damien
 * Date: 15/04/2016
 * Time: 01:39
 */

namespace FD\OfferBundle\Controller;

use FD\OfferBundle\Entity\Offer;
use FD\OfferBundle\Entity\Outcome;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Constraints\DateTime;

class OfferController extends Controller
{
    public function get1N2Action()
    {
        ini_set('memory_limit', '2048M');
        ini_set('max_execution_time', 6000);

        $cptPersist = 0;

        $offerInformationsArray = array();
        $apiContentFootball = file_get_contents('https://www.parionssport.fr/api/1n2/offre?sport=100');
        $offerFootballInformations = json_decode($apiContentFootball);
        $apiContentBasketball = file_get_contents('https://www.parionssport.fr/api/1n2/offre?sport=601600');
        $offerBasketballInformations = json_decode($apiContentBasketball);
        $apiContentTennis = file_get_contents('https://www.parionssport.fr/api/1n2/offre?sport=600');
        $offerTennisInformations = json_decode($apiContentTennis);
        $apiContentRugby = file_get_contents('https://www.parionssport.fr/api/1n2/offre?sport=964500');
        $offerRugbyInformations = json_decode($apiContentRugby);
        $apiContentVolleyball = file_get_contents('https://www.parionssport.fr/api/1n2/offre?sport=1200');
        $offerVolleyballInformations = json_decode($apiContentVolleyball);
        $apiContentHandball = file_get_contents('https://www.parionssport.fr/api/1n2/offre?sport=1100');
        $offerHandballInformations = json_decode($apiContentHandball);
        $apiContentHockey = file_get_contents('https://www.parionssport.fr/api/1n2/offre?sport=2100');
        $offerHockeyInformations = json_decode($apiContentHockey);
        $apiContentUSFootball = file_get_contents('https://www.parionssport.fr/api/1n2/offre?sport=700');
        $offerUSFootballInformations = json_decode($apiContentUSFootball);
        array_push($offerInformationsArray, $offerFootballInformations);
        array_push($offerInformationsArray, $offerBasketballInformations);
        array_push($offerInformationsArray, $offerTennisInformations);
        array_push($offerInformationsArray, $offerRugbyInformations);
        array_push($offerInformationsArray, $offerVolleyballInformations);
        array_push($offerInformationsArray, $offerHandballInformations);
        array_push($offerInformationsArray, $offerHockeyInformations);
        array_push($offerInformationsArray, $offerUSFootballInformations);

        $em = $this->getDoctrine()->getManager('default');
        $offerRepository = $em->getRepository('FDOfferBundle:Offer');
        foreach($offerInformationsArray as $offerBySport)
        {
            if(!empty($offerBySport)) {

                foreach ($offerBySport as $offerItem) {
                    if (substr_count($offerItem->label, '-') == 1) {
                        $resultQuery = $offerRepository->findBy(array('eventId' => $offerItem->eventId));
                        if (empty($resultQuery)) {
                            $offer = new Offer();
                            $offer->setCompetitionId($offerItem->competitionId);
                            $offer->setDate(\DateTime::createFromFormat('Y-m-d', substr($offerItem->end, 0, 10)));
                            $offer->setEventId($offerItem->eventId);
                            $offer->setFDJNumber($offerItem->index);
                            $offer->setLabel($offerItem->label);
                            $offer->setSportId($offerItem->sportId);
                            $offer->setMarketType('1N2');
                            $em->persist($offer);
                            $cptPersist++;
                            if ($cptPersist == 1000) {
                                $em->flush();
                                $cptPersist = 0;
                            }

                            foreach ($offerItem->outcomes as $outcomeItem) {
                                $outcome = new Outcome();
                                $outcome->setCote(floatval(str_replace(",", ".", $outcomeItem->cote)));
                                $outcome->setOffer($offer);

                                $em->persist($outcome);
                                $cptPersist++;

                                if ($cptPersist == 1000) {
                                    $em->flush();
                                    $cptPersist = 0;
                                }
                            }
                        }
                    }
                }
            }
        }

        if($cptPersist > 0)
        {
            $em->flush();
        }

        var_dump("Offer Get OK");

        return new Response("Hello World");
    }
}
