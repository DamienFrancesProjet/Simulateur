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
        ini_set('max_execution_time', 18000);
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

                    $resultQuery = $offerRepository->findBy(array('eventId' => $offerItem->eventId));
                    if(empty($resultQuery)) {
                        $offer = new Offer();
                        $offer->setCompetitionId($offerItem->competitionId);
                        $offer->setDate(\DateTime::createFromFormat('Y-m-d', substr($offerItem->end, 0, 10)));
                        $offer->setEventId($offerItem->eventId);
                        $offer->setFDJNumber($offerItem->index);
                        $offer->setLabel($offerItem->label);
                        $offer->setSportId($offerItem->sportId);
                        $offer->setMarketType('1N2');
                        $em->persist($offer);
                        $em->flush();

                        foreach ($offerItem->outcomes as $outcomeItem) {
                            $outcome = new Outcome();
                            $outcome->setCote(floatval(str_replace(",", ".", $outcomeItem->cote)));
                            $outcome->setOffer($offer);

                            $em->persist($outcome);
                            $em->flush();
                        }
                    }
                }
            }
        }


        //var_dump($offerInformationsArray[0][0]->outcomes);

        //var_dump($offerInformationsArray[0][0]->formules);

        //var_dump($offerInformationsArray[0][0]->formules[0]->outcomes);

        return new Response("Hello World");
    }
}

/*$repositoryOffer = $this->getDoctrine()->getManager()->getRepository('BSOfferBundle:Offer');
$repositoryOutcome = $this->getDoctrine()->getManager()->getRepository('BSOfferBundle:Outcome');

foreach($offerInformationsArray as $offerInformations)
{
    if($offerInformations != null) // V�rification qu'il y a bien des offres sur le sport que l'on va traiter
    {
        foreach ($offerInformations as $offer)
        {
            $localOffer = new Offer();
            $localOffer->setEventId($offer->eventId);
            $localOffer->setMarketId($offer->marketId);
            $localOffer->setSportId($offer->sportId);
            $localOffer->setIndexOffer($offer->index);
            $localOffer->setMarketType($offer->marketType);
            $localOffer->setEnd($offer->end);
            $localOffer->setLabelOffer($offer->label);
            $localOffer->setCompetition($offer->competition);
            $localOffer->setCompetitionId($offer->competitionId);
            foreach ($offer->outcomes as $outcomes) {
                $localOutcome = new Outcome();
                $localOutcome->setOffer($localOffer);
                if($outcomes->label == "1")
                {
                    $localOutcomeLabel = "Domicile";
                    $localOutcome->setLabelOutcome("$localOutcomeLabel");
                }
                elseif($outcomes->label == "2")
                {
                    $localOutcomeLabel = "Exterieur";

                    $localOutcome->setLabelOutcome($localOutcomeLabel);
                }
                else {
                    $localOutcomeLabel = $outcomes->label;
                    $localOutcome->setLabelOutcome($localOutcomeLabel);
                }
                $localOutcome->setCote($outcomes->cote);
                $localOutcome->setEventId($offer->eventId);
                $localOutcome->setIndexOffer($offer->index);
                $duplicateOutcome = $repositoryOutcome->verifyDuplicate($localOutcomeLabel, $offer);
                if(empty($duplicateOutcome))
                {
                    $em = $this->getDoctrine()->getManager();
                    $em->persist($localOutcome);
                    $em->flush();
                }
            }
            foreach ($offer->formules as $formule) {
                $localOfferFormule = new Offer();
                $localOfferFormule->setEventId($formule->eventId);
                $localOfferFormule->setMarketId($offer->marketId);
                $localOfferFormule->setSportId($formule->sportId);
                $localOfferFormule->setIndexOffer($formule->index);
                $localOfferFormule->setMarketType($formule->marketType);
                $localOfferFormule->setEnd($formule->end);
                $localOfferFormule->setLabelOffer($formule->label);
                $localOfferFormule->setCompetition($formule->competition);
                $localOfferFormule->setCompetitionId($offer->competitionId);
                foreach ($formule->outcomes as $outcomes) {
                    $localOutcome = new Outcome();
                    $localOutcome->setOffer($localOfferFormule);
                    if($outcomes->label == "1")
                    {
                        $localOutcomeLabel = "Domicile";
                        $localOutcome->setLabelOutcome("$localOutcomeLabel");
                    }
                    elseif($outcomes->label == "2")
                    {
                        $localOutcomeLabel = "Exterieur";

                        $localOutcome->setLabelOutcome($localOutcomeLabel);
                    }
                    else {
                        $localOutcomeLabel = $outcomes->label;
                        $localOutcome->setLabelOutcome($localOutcomeLabel);
                    }
                    $localOutcome->setCote($outcomes->cote);
                    $localOutcome->setEventId($formule->eventId);
                    $localOutcome->setIndexOffer($formule->index);
                    $duplicateOutcome = $repositoryOutcome->verifyDuplicate($localOutcomeLabel, $formule);
                    if(empty($duplicateOutcome))
                    {
                        $em = $this->getDoctrine()->getManager();
                        $em->persist($localOutcome);
                        $em->flush();
                    }
                }

                $duplicateFormule = $repositoryOffer->verifyDuplicate($formule);
                if(empty($duplicateFormule)) {
                    $em = $this->getDoctrine()->getManager();
                    $em->persist($localOfferFormule);
                    $em->flush();
                }
            }
            $duplicateOffer = $repositoryOffer->verifyDuplicate($offer);
            if(empty($duplicateOffer)) {
                $em = $this->getDoctrine()->getManager();
                $em->persist($localOffer);
                $em->flush();
            }
        }
    }
}


return new Response("Hello World");
}*/
