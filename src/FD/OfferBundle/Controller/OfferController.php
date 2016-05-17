<?php
/**
 * Created by PhpStorm.
 * User: Damien
 * Date: 15/04/2016
 * Time: 01:39
 */

namespace FD\OfferBundle\Controller;

use DateTime;
use FD\OfferBundle\Entity\Offer;
use FD\OfferBundle\Entity\Outcome;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class OfferController extends Controller
{
    /*public function indexAction()
    {
        $em = $this->getDoctrine()->getManager('default');
        $outcomeRepository = $em->getRepository('FDOfferBundle:Outcome');
        $offerRepository = $em->getRepository('FDOfferBundle:Offer');

        $offers = array();

        $offerIds = $outcomeRepository->findOfferOfTheDayDistinct(substr((new DateTime())->format('Y-m-d'), 0, 10));

        foreach($offerIds as $offerId)
        {
            array_push($offers, $offerRepository->find($offerId[1]));
        }

        return $this->render('FDOfferBundle:Offer:index.html.twig', array("offers" => $offers));
    }*/

    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager('default');
        $offerRepository = $em->getRepository('FDOfferBundle:Offer');
        $betRepository = $em->getRepository('FDBetBundle:Bet');
        $marketResultRepository = $em->getRepository('FDResultBundle:MarketResult');
        $outcomeRepository = $em->getRepository('FDOfferBundle:Outcome');
        $date = new DateTime();

        $eventIds = $offerRepository->findEventIdOfTheDay(substr($date->format('Y-m-d'), 0, 10));

        $offers = array();


        foreach($eventIds as $eventIdArray)
        {
            $eventId = $eventIdArray['eventId'];
            $offer = array();
            $offer['label'] = $offerRepository->findLabelByEventId($eventId)[0]['label'];
            $offer['fdjNumber'] = $offerRepository->findFdjNumberByEventId($eventId)[0]['fDJNumber'];
            $offer['sportId'] = $offerRepository->findSportIdByEventId($eventId)[0]['sportId'];


            $bet = $betRepository->findBetAndOutcomeByEventId($eventId);
            if(!empty($bet))
            {
                $outcome = $outcomeRepository->find($bet[0][1]);
                $offerId = $outcomeRepository->findOfferIdByOutcomeId($bet[0][1]);
                $outcomes = $outcomeRepository->findBy(array('offer' => $offerRepository->find($offerId[0][1])));
                if(sizeof($outcomes) == 3)
                {
                    if($outcomes[0] == $outcome)
                    {
                        $offer['bet'] = '1';
                        $offer['cote'] = $outcome->getCote();
                    }
                    elseif($outcomes[1] == $outcome)
                    {
                        $offer['bet'] = 'N';
                        $offer['cote'] = $outcome->getCote();

                    }
                    else
                    {
                        $offer['bet'] = '2';
                        $offer['cote'] = $outcome->getCote();

                    }
                }
                else
                {
                    if($outcomes[0] == $outcome)
                    {
                        $offer['bet'] = '1';
                        $offer['cote'] = $outcome->getCote();

                    }
                    else
                    {
                        $offer['bet'] = '2';
                        $offer['cote'] = $outcome->getCote();
                    }
                }
            }
            else
            {
                $offer['bet'] = null;
                $offer['cote'] = null;
            }

            $resultat = $marketResultRepository->findResultatByEventId($eventId);
            if(!empty($resultat))
            {
                $offer['resultat'] = $resultat[0]['resultat'];
            }
            else
            {
                $offer['resultat'] = null;
            }

            array_push($offers, $offer);


        }

        return $this->render('FDOfferBundle:Offer:index.html.twig', array('offers' => $offers));
    }

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

        $betRepository = $em->getRepository('FDBetBundle:Bet');
        $marketResultRepository = $em->getRepository('FDResultBundle:MarketResult');
        $outcomeRepository = $em->getRepository('FDOfferBundle:Outcome');
        $date = new DateTime();

        $eventIds = $offerRepository->findEventIdOfTheDay(substr($date->format('Y-m-d'), 0, 10));

        $offers = array();


        foreach($eventIds as $eventIdArray)
        {
            $eventId = $eventIdArray['eventId'];
            $offer = array();
            $offer['label'] = $offerRepository->findLabelByEventId($eventId)[0]['label'];
            $offer['fdjNumber'] = $offerRepository->findFdjNumberByEventId($eventId)[0]['fDJNumber'];
            $offer['sportId'] = $offerRepository->findSportIdByEventId($eventId)[0]['sportId'];


            $bet = $betRepository->findBetAndOutcomeByEventId($eventId);
            if(!empty($bet))
            {
                $outcome = $outcomeRepository->find($bet[0][1]);
                $offerId = $outcomeRepository->findOfferIdByOutcomeId($bet[0][1]);
                $outcomes = $outcomeRepository->findBy(array('offer' => $offerRepository->find($offerId[0][1])));
                if(sizeof($outcomes) == 3)
                {
                    if($outcomes[0] == $outcome)
                    {
                        $offer['bet'] = '1';
                        $offer['cote'] = $outcome->getCote();
                    }
                    elseif($outcomes[1] == $outcome)
                    {
                        $offer['bet'] = 'N';
                        $offer['cote'] = $outcome->getCote();

                    }
                    else
                    {
                        $offer['bet'] = '2';
                        $offer['cote'] = $outcome->getCote();

                    }
                }
                else
                {
                    if($outcomes[0] == $outcome)
                    {
                        $offer['bet'] = '1';
                        $offer['cote'] = $outcome->getCote();

                    }
                    else
                    {
                        $offer['bet'] = '2';
                        $offer['cote'] = $outcome->getCote();
                    }
                }
            }
            else
            {
                $offer['bet'] = null;
                $offer['cote'] = null;
            }

            $resultat = $marketResultRepository->findResultatByEventId($eventId);
            if(!empty($resultat))
            {
                $offer['resultat'] = $resultat[0]['resultat'];
            }
            else
            {
                $offer['resultat'] = null;
            }

            array_push($offers, $offer);


        }

        return $this->render('FDOfferBundle:Offer:index.html.twig', array('offers' => $offers));
    }
}
