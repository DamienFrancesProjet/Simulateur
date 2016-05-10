<?php
/**
 * Created by PhpStorm.
 * User: Damien
 * Date: 15/04/2016
 * Time: 01:39
 */

namespace FD\BetBundle\Controller;

use DateTime;
use FD\BetBundle\Entity\Bet;
use FD\BetBundle\Entity\Strategy;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class BetController extends Controller
{
    public function betIndexAction()
    {
        $em = $this->getDoctrine()->getManager('default');
        $strategyRepository = $em->getRepository('FDBetBundle:Strategy');
        $strategies = $strategyRepository->findAll();
        return $this->render('FDBetBundle:Bet:index.html.twig', array('strategies' => $strategies));
    }

    public function updateBetAction()
    {
        $em = $this->getDoctrine()->getManager('default');
        $cptPersist = 0;
        $betRepostory = $em->getRepository('FDBetBundle:Bet');
        $bets = $betRepostory->findBy(array('marketResult' => null));
        $resultRepository = $em->getRepository('FDResultBundle:Result');
        $marketResultRepository = $em->getRepository('FDResultBundle:MarketResult');
        $strategyRepository = $em->getRepository('FDBetBundle:Strategy');


        foreach ($bets as $bet) {
            $outcome = $bet->getOutcome();
            $offer = $outcome->getOffer();
            $eventId = $offer->getEventId();

            $results = $resultRepository->findBy(array('eventId' => $eventId));

            if (!empty($results)) {

                $marketResult = $marketResultRepository->findBy(array('result' => $results[0]));
                $bet->setMarketResult($marketResult[0]);
                $em->persist($bet);
                $cptPersist++;

                if ($cptPersist == 1000) {
                    $em->flush();
                    $cptPersist = 0;
                }
            }
        }



        if ($cptPersist > 0) {
            $em->flush();
        }

        $strategies = $strategyRepository->findAll();
        return $this->render('FDBetBundle:Bet:index.html.twig', array("strategies" => $strategies));


    }

    public function strategyCalculatingAction()
    {
        $em = $this->getDoctrine()->getManager('default');
        $strategyReposiory = $em->getRepository('FDBetBundle:Strategy');
        $betRepository = $em->getRepository('FDBetBundle:Bet');
        $outcomeRepository = $em->getRepository('FDOfferBundle:Outcome');
        $marketResultRepository = $em->getRepository('FDResultBundle:MarketResult');
        $strategyRepository = $em->getRepository('FDBetBundle:Strategy');

        $cptPersist = 0;
        $strategies = $strategyReposiory->findAll();
        foreach ($strategies as $strategy) {
            $strategy->setMoneyEarned(0);
            $strategy->setMoneySpent(0);
            $bets = $betRepository->findBy(array('strategy' => $strategy));

            foreach ($bets as $bet) {
                if ($bet->getMarketResult() != null) {
                    $outcome = $bet->getOutcome();
                    $offer = $outcome->getOffer();
                    $eventId = $offer->getEventId();

                    $nbOutcome = $outcomeRepository->countByOffer($offer);
                    $outcomes = $outcomeRepository->findBy(array('offer' => $offer));
                    $marketResult = $marketResultRepository->findByEventId($eventId);
                    switch ($nbOutcome[0][1]) {
                        case '2':
                            if ($outcome == $outcomes[0]) {
                                if ($marketResult[0]->getResultat() == '1') {
                                    $strategy->setMoneyEarned($strategy->getMoneyEarned() + $outcome->getCote());
                                }
                                $strategy->setMoneySpent($strategy->getMoneySpent() + 1);
                            } else {
                                if ($marketResult[0]->getResultat() == '2') {
                                    $strategy->setMoneyEarned($strategy->getMoneyEarned() + $outcome->getCote());
                                }
                                $strategy->setMoneySpent($strategy->getMoneySpent() + 1);
                            }
                            break;
                        case '3':
                            if ($outcome == $outcomes[0]) {
                                if ($marketResult[0]->getResultat() == '1') {
                                    $strategy->setMoneyEarned($strategy->getMoneyEarned() + $outcome->getCote());
                                }
                                $strategy->setMoneySpent($strategy->getMoneySpent() + 1);
                            } elseif ($outcome == $outcomes[1]) {
                                if ($marketResult[0]->getResultat() == 'N') {
                                    $strategy->setMoneyEarned($strategy->getMoneyEarned() + $outcome->getCote());
                                }
                                $strategy->setMoneySpent($strategy->getMoneySpent() + 1);
                            } else {
                                if ($marketResult[0]->getResultat() == '2') {
                                    $strategy->setMoneyEarned($strategy->getMoneyEarned() + $outcome->getCote());
                                }
                                $strategy->setMoneySpent($strategy->getMoneySpent() + 1);
                            }
                            break;
                    }
                    $em->persist($strategy);
                    $cptPersist++;

                    if ($cptPersist == 1000) {
                        $em->flush();
                        $cptPersist = 0;
                    }
                }

            }


        }

        $strategies = $strategyRepository->findAll();
        foreach($strategies as $strategy)
        {
            $strategy->setWaiting(false);
            $em->persist($strategy);
            $cptPersist++;
        }

        if ($cptPersist > 0) {
            $em->flush();
        }

        return $this->render('FDBetBundle:Bet:index.html.twig', array("strategies" => $strategies));



    }

    public function addStrategyAction($label)
    {
        $em = $this->getDoctrine()->getManager('default');

        $strategy = new Strategy();
        $strategy->setLabel($label);
        $em->persist($strategy);
        $em->flush();

        return new Response("Hello World");

    }










    public function strategyLastVictoryHigherRankHomeLastDefeatAwayAction()
    {
        ini_set('memory_limit', '2048M');
        ini_set('max_execution_time', 6000);


        $em = $this->getDoctrine()->getManager('default');
        $outcomeRepository = $em->getRepository('FDOfferBundle:Outcome');
        $offerRepository = $em->getRepository('FDOfferBundle:Offer');
        $strategyRepository = $em->getRepository('FDBetBundle:Strategy');
        $betRepository = $em->getRepository('FDBetBundle:Bet');
        $teamRepository = $em->getRepository('FDTeamBundle:Team');
        $marketResultRepository = $em->getRepository('FDResultBundle:MarketResult');

        $strategies = $strategyRepository->findBy(array('label' => 'LastVictoryHigherRankHomeLastDefeatAway'));

        $bets = $betRepository->findBy(array('strategy' => $strategies[0]));
        $maxOutcome = $bets[sizeof($bets)-1]->getOutcome();

        $offerIds = $outcomeRepository->findAllOfferDistinctHtId($maxOutcome->getId());

        foreach ($offerIds as $offerId) {
            $offer = $offerRepository->find($offerId[1]);
            $date = $offer->getDate();
            $labelSplit = explode('-', $offer->getLabel());
            $competitionId = $offer->getCompetitionId();
            $homeTeamLabel = $labelSplit[0];
            $awayTeamLabel = $labelSplit[1];

            if(substr($homeTeamLabel, -1)== ' ')
            {
                $homeTeamLabel = substr($homeTeamLabel, 0, -1);
            }
            if(substr($awayTeamLabel, -1)== ' ')
            {
                $awayTeamLabel = substr($awayTeamLabel, 0, -1);
            }

            $homeTeam = $teamRepository->findBy(array('label' => $homeTeamLabel, 'competitionId' => $competitionId));
            $awayTeam = $teamRepository->findBy(array('label' => $awayTeamLabel, 'competitionId' => $competitionId));
            if(!empty($homeTeam) && !empty($awayTeam)) {
                $teams = [$homeTeam, $awayTeam];

                $nbHomeMarketResult = $marketResultRepository->findByLabelAndCompetitionIdAndDate($homeTeam[0]->getLabel(), $homeTeam[0]->getCompetitionId(), $date);
                $nbAwayMarketResult = $marketResultRepository->findByLabelAndCompetitionIdAndDate($awayTeam[0]->getLabel(), $awayTeam[0]->getCompetitionId(), $date);
                if(count($nbHomeMarketResult) > count($nbAwayMarketResult))
                {
                    $nbMarketResult = count($nbAwayMarketResult);
                }
                else
                {
                    $nbMarketResult = count($nbHomeMarketResult);
                }

                $cptPersist = 0;

                foreach ($teams as $teamItem) {
                    $team = $teamItem[0];
                    $team->setPoints(0);
                    $team->setSerie('');
                    $teamLabel = $team->getLabel();
                    $marketResultsNoCut = $marketResultRepository->findByLabelAndCompetitionIdAndDate($teamLabel, $team->getCompetitionId(), $date);
                    $marketResults = array_slice($marketResultsNoCut, 0, $nbMarketResult);
                    foreach ($marketResults as $marketResult) {
                        $result = $marketResult->getResult();
                        $resultLabel = $result->getLabel();
                        $resultLabelSplit = explode('-', $resultLabel);
                        $resultat = $marketResult->getResultat();
                        if(substr($resultLabelSplit[0], -1)== ' ')
                        {
                            $resultLabelSplit[0] = substr($resultLabelSplit[0], 0, -1);
                        }
                        if ($resultLabelSplit[0] == $teamLabel) {
                            switch ($resultat) {
                                case '1':
                                    $team->setPoints($team->getPoints() + 3);
                                    $team->setSerie( $team->getSerie().'V');
                                    break;
                                case 'N':
                                    $team->setPoints($team->getPoints() + 1);
                                    $team->setSerie($team->getSerie().'N');
                                    break;
                                case '2':
                                    $team->setSerie($team->getSerie().'D');
                                    break;
                            }
                        } else {
                            switch ($resultat) {
                                case '1':
                                    $team->setSerie($team->getSerie().'D');
                                    break;
                                case 'N':
                                    $team->setPoints($team->getPoints() + 1);
                                    $team->setSerie($team->getSerie().'N');
                                    break;
                                case '2':
                                    $team->setPoints($team->getPoints() + 3);
                                    $team->setSerie($team->getSerie().'V');
                                    break;
                            }
                        }
                    }
                    $em->persist($team);
                    $cptPersist++;

                    if ($cptPersist == 1000) {
                        $em->flush();
                        $cptPersist = 0;
                    }
                }

                if ($cptPersist > 0) {
                    $em->flush();
                }
            }
            $outcomes = $outcomeRepository->findBy(array('offer' => $offer));

            $resultQuery = $betRepository->findBy(array('outcome' => $outcomes[0], 'strategy' => $strategies[0]));
            if (empty($resultQuery)) {


                $resultQueryHomeTeam = $teamRepository->findBy(array('label' => $labelSplit[0], 'competitionId' => $competitionId));
                $resultQueryAwayTeam = $teamRepository->findBy(array('label' => $labelSplit[1], 'competitionId' => $competitionId));
                if (!empty($resultQueryHomeTeam) && !empty($resultQueryAwayTeam)) {
                    $homeTeam = $resultQueryHomeTeam[0];
                    $awayTeam = $resultQueryAwayTeam[0];
                    $homeSerie = $homeTeam->getSerie();
                    $awaySerie = $awayTeam->getSerie();
                    if (strlen($awaySerie) > 0 && strlen($homeSerie) > 0) {
                        if ($homeSerie[0] == 'V' && $awaySerie[0] == 'D' && ($homeTeam->getPoints() > $awayTeam->getPoints())) {
                            $bet = new Bet();
                            $bet->setStrategy($strategies[0]);
                            $bet->setOutcome($outcomes[0]);
                            $em->persist($bet);
                            $em->flush();


                        }
                    }
                }
            }
        }

        $strategies[0]->setWaiting(true);
        $em->persist($strategies[0]);
        $em->flush();

        $strategies = $strategyRepository->findAll();


        return $this->render('FDBetBundle:Bet:index.html.twig', array("strategies" => $strategies));

    }







    public function strategy2LastVictoryHigherRankHomeLastDefeatAwayAction()
    {
        ini_set('memory_limit', '2048M');
        ini_set('max_execution_time', 6000);


        $em = $this->getDoctrine()->getManager('default');
        $outcomeRepository = $em->getRepository('FDOfferBundle:Outcome');
        $offerRepository = $em->getRepository('FDOfferBundle:Offer');
        $strategyRepository = $em->getRepository('FDBetBundle:Strategy');
        $betRepository = $em->getRepository('FDBetBundle:Bet');
        $teamRepository = $em->getRepository('FDTeamBundle:Team');
        $marketResultRepository = $em->getRepository('FDResultBundle:MarketResult');


        $strategies = $strategyRepository->findBy(array('label' => '2LastVictoryHigherRankHomeLastDefeatAway'));

        $bets = $betRepository->findBy(array('strategy' => $strategies[0]));
        $maxOutcome = $bets[sizeof($bets)-1]->getOutcome();

        $offerIds = $outcomeRepository->findAllOfferDistinctHtId($maxOutcome->getId());

        foreach ($offerIds as $offerId) {
            $offer = $offerRepository->find($offerId[1]);
            $date = $offer->getDate();
            $labelSplit = explode('-', $offer->getLabel());
            $competitionId = $offer->getCompetitionId();
            $homeTeamLabel = $labelSplit[0];
            $awayTeamLabel = $labelSplit[1];

            if(substr($homeTeamLabel, -1)== ' ')
            {
                $homeTeamLabel = substr($homeTeamLabel, 0, -1);
            }
            if(substr($awayTeamLabel, -1)== ' ')
            {
                $awayTeamLabel = substr($awayTeamLabel, 0, -1);
            }

            $homeTeam = $teamRepository->findBy(array('label' => $homeTeamLabel, 'competitionId' => $competitionId));
            $awayTeam = $teamRepository->findBy(array('label' => $awayTeamLabel, 'competitionId' => $competitionId));
            if(!empty($homeTeam) && !empty($awayTeam)) {
                $teams = [$homeTeam, $awayTeam];

                $nbHomeMarketResult = $marketResultRepository->findByLabelAndCompetitionIdAndDate($homeTeam[0]->getLabel(), $homeTeam[0]->getCompetitionId(), $date);
                $nbAwayMarketResult = $marketResultRepository->findByLabelAndCompetitionIdAndDate($awayTeam[0]->getLabel(), $awayTeam[0]->getCompetitionId(), $date);
                if(count($nbHomeMarketResult) > count($nbAwayMarketResult))
                {
                    $nbMarketResult = count($nbAwayMarketResult);
                }
                else
                {
                    $nbMarketResult = count($nbHomeMarketResult);
                }

                $cptPersist = 0;

                foreach ($teams as $teamItem) {
                    $team = $teamItem[0];
                    $team->setPoints(0);
                    $team->setSerie('');
                    $teamLabel = $team->getLabel();
                    $marketResultsNoCut = $marketResultRepository->findByLabelAndCompetitionIdAndDate($teamLabel, $team->getCompetitionId(), $date);
                    $marketResults = array_slice($marketResultsNoCut, 0, $nbMarketResult);
                    foreach ($marketResults as $marketResult) {
                        $result = $marketResult->getResult();
                        $resultLabel = $result->getLabel();
                        $resultLabelSplit = explode('-', $resultLabel);
                        $resultat = $marketResult->getResultat();
                        if(substr($resultLabelSplit[0], -1)== ' ')
                        {
                            $resultLabelSplit[0] = substr($resultLabelSplit[0], 0, -1);
                        }
                        if ($resultLabelSplit[0] == $teamLabel) {
                            switch ($resultat) {
                                case '1':
                                    $team->setPoints($team->getPoints() + 3);
                                    $team->setSerie( $team->getSerie().'V');
                                    break;
                                case 'N':
                                    $team->setPoints($team->getPoints() + 1);
                                    $team->setSerie($team->getSerie().'N');
                                    break;
                                case '2':
                                    $team->setSerie($team->getSerie().'D');
                                    break;
                            }
                        } else {
                            switch ($resultat) {
                                case '1':
                                    $team->setSerie($team->getSerie().'D');
                                    break;
                                case 'N':
                                    $team->setPoints($team->getPoints() + 1);
                                    $team->setSerie($team->getSerie().'N');
                                    break;
                                case '2':
                                    $team->setPoints($team->getPoints() + 3);
                                    $team->setSerie($team->getSerie().'V');
                                    break;
                            }
                        }
                    }
                    $em->persist($team);
                    $cptPersist++;

                    if ($cptPersist == 1000) {
                        $em->flush();
                        $cptPersist = 0;
                    }
                }

                if ($cptPersist > 0) {
                    $em->flush();
                }
            }
            $outcomes = $outcomeRepository->findBy(array('offer' => $offer));

            $resultQuery = $betRepository->findBy(array('outcome' => $outcomes[0], 'strategy' => $strategies[0]));
            if (empty($resultQuery)) {


                $resultQueryHomeTeam = $teamRepository->findBy(array('label' => $labelSplit[0], 'competitionId' => $competitionId));
                $resultQueryAwayTeam = $teamRepository->findBy(array('label' => $labelSplit[1], 'competitionId' => $competitionId));
                if (!empty($resultQueryHomeTeam) && !empty($resultQueryAwayTeam)) {
                    $homeTeam = $resultQueryHomeTeam[0];
                    $awayTeam = $resultQueryAwayTeam[0];
                    $homeSerie = $homeTeam->getSerie();
                    $awaySerie = $awayTeam->getSerie();
                    if (strlen($awaySerie) > 0 && strlen($homeSerie) > 1) {
                        if ($homeSerie[0] == 'V' && $homeSerie[1] == 'V' && $awaySerie[0] == 'D' && ($homeTeam->getPoints() > $awayTeam->getPoints())) {
                            $bet = new Bet();
                            $bet->setStrategy($strategies[0]);
                            $bet->setOutcome($outcomes[0]);
                            $em->persist($bet);
                            $em->flush();


                        }
                    }
                }
            }
        }

        $strategies[0]->setWaiting(true);
        $em->persist($strategies[0]);
        $em->flush();

        $strategies = $strategyRepository->findAll();


        return $this->render('FDBetBundle:Bet:index.html.twig', array("strategies" => $strategies));

    }







    public function strategyLastVictoryHigherRankHome2LastDefeatAwayAction()
    {
        ini_set('memory_limit', '2048M');
        ini_set('max_execution_time', 6000);


        $em = $this->getDoctrine()->getManager('default');
        $outcomeRepository = $em->getRepository('FDOfferBundle:Outcome');
        $offerRepository = $em->getRepository('FDOfferBundle:Offer');
        $strategyRepository = $em->getRepository('FDBetBundle:Strategy');
        $betRepository = $em->getRepository('FDBetBundle:Bet');
        $teamRepository = $em->getRepository('FDTeamBundle:Team');
        $marketResultRepository = $em->getRepository('FDResultBundle:MarketResult');


        $strategies = $strategyRepository->findBy(array('label' => 'LastVictoryHigherRankHome2LastDefeatAway'));

        $bets = $betRepository->findBy(array('strategy' => $strategies[0]));
        $maxOutcome = $bets[sizeof($bets)-1]->getOutcome();

        $offerIds = $outcomeRepository->findAllOfferDistinctHtId($maxOutcome->getId());
        foreach ($offerIds as $offerId) {
            $offer = $offerRepository->find($offerId[1]);
            $date = $offer->getDate();
            $labelSplit = explode('-', $offer->getLabel());
            $competitionId = $offer->getCompetitionId();
            $homeTeamLabel = $labelSplit[0];
            $awayTeamLabel = $labelSplit[1];

            if(substr($homeTeamLabel, -1)== ' ')
            {
                $homeTeamLabel = substr($homeTeamLabel, 0, -1);
            }
            if(substr($awayTeamLabel, -1)== ' ')
            {
                $awayTeamLabel = substr($awayTeamLabel, 0, -1);
            }

            $homeTeam = $teamRepository->findBy(array('label' => $homeTeamLabel, 'competitionId' => $competitionId));
            $awayTeam = $teamRepository->findBy(array('label' => $awayTeamLabel, 'competitionId' => $competitionId));
            if(!empty($homeTeam) && !empty($awayTeam)) {
                $teams = [$homeTeam, $awayTeam];

                $nbHomeMarketResult = $marketResultRepository->findByLabelAndCompetitionIdAndDate($homeTeam[0]->getLabel(), $homeTeam[0]->getCompetitionId(), $date);
                $nbAwayMarketResult = $marketResultRepository->findByLabelAndCompetitionIdAndDate($awayTeam[0]->getLabel(), $awayTeam[0]->getCompetitionId(), $date);
                if(count($nbHomeMarketResult) > count($nbAwayMarketResult))
                {
                    $nbMarketResult = count($nbAwayMarketResult);
                }
                else
                {
                    $nbMarketResult = count($nbHomeMarketResult);
                }

                $cptPersist = 0;

                foreach ($teams as $teamItem) {
                    $team = $teamItem[0];
                    $team->setPoints(0);
                    $team->setSerie('');
                    $teamLabel = $team->getLabel();
                    $marketResultsNoCut = $marketResultRepository->findByLabelAndCompetitionIdAndDate($teamLabel, $team->getCompetitionId(), $date);
                    $marketResults = array_slice($marketResultsNoCut, 0, $nbMarketResult);
                    foreach ($marketResults as $marketResult) {
                        $result = $marketResult->getResult();
                        $resultLabel = $result->getLabel();
                        $resultLabelSplit = explode('-', $resultLabel);
                        $resultat = $marketResult->getResultat();
                        if(substr($resultLabelSplit[0], -1)== ' ')
                        {
                            $resultLabelSplit[0] = substr($resultLabelSplit[0], 0, -1);
                        }
                        if ($resultLabelSplit[0] == $teamLabel) {
                            switch ($resultat) {
                                case '1':
                                    $team->setPoints($team->getPoints() + 3);
                                    $team->setSerie( $team->getSerie().'V');
                                    break;
                                case 'N':
                                    $team->setPoints($team->getPoints() + 1);
                                    $team->setSerie($team->getSerie().'N');
                                    break;
                                case '2':
                                    $team->setSerie($team->getSerie().'D');
                                    break;
                            }
                        } else {
                            switch ($resultat) {
                                case '1':
                                    $team->setSerie($team->getSerie().'D');
                                    break;
                                case 'N':
                                    $team->setPoints($team->getPoints() + 1);
                                    $team->setSerie($team->getSerie().'N');
                                    break;
                                case '2':
                                    $team->setPoints($team->getPoints() + 3);
                                    $team->setSerie($team->getSerie().'V');
                                    break;
                            }
                        }
                    }
                    $em->persist($team);
                    $cptPersist++;

                    if ($cptPersist == 1000) {
                        $em->flush();
                        $cptPersist = 0;
                    }
                }

                if ($cptPersist > 0) {
                    $em->flush();
                }
            }
            $outcomes = $outcomeRepository->findBy(array('offer' => $offer));

            $resultQuery = $betRepository->findBy(array('outcome' => $outcomes[0], 'strategy' => $strategies[0]));
            if (empty($resultQuery)) {


                $resultQueryHomeTeam = $teamRepository->findBy(array('label' => $labelSplit[0], 'competitionId' => $competitionId));
                $resultQueryAwayTeam = $teamRepository->findBy(array('label' => $labelSplit[1], 'competitionId' => $competitionId));
                if (!empty($resultQueryHomeTeam) && !empty($resultQueryAwayTeam)) {
                    $homeTeam = $resultQueryHomeTeam[0];
                    $awayTeam = $resultQueryAwayTeam[0];
                    $homeSerie = $homeTeam->getSerie();
                    $awaySerie = $awayTeam->getSerie();
                    if (strlen($awaySerie) > 1 && strlen($homeSerie) > 0) {
                        if ($homeSerie[0] == 'V' && $awaySerie[1] == 'D' && $awaySerie[0] == 'D' && ($homeTeam->getPoints() > $awayTeam->getPoints())) {
                            $bet = new Bet();
                            $bet->setStrategy($strategies[0]);
                            $bet->setOutcome($outcomes[0]);
                            $em->persist($bet);
                            $em->flush();


                        }
                    }
                }
            }
        }

        $strategies[0]->setWaiting(true);
        $em->persist($strategies[0]);
        $em->flush();

        $strategies = $strategyRepository->findAll();


        return $this->render('FDBetBundle:Bet:index.html.twig', array("strategies" => $strategies));

    }







    public function strategy2LastVictoryHigherRankHome2LastDefeatAwayAction()
    {
        ini_set('memory_limit', '2048M');
        ini_set('max_execution_time', 6000);


        $em = $this->getDoctrine()->getManager('default');
        $outcomeRepository = $em->getRepository('FDOfferBundle:Outcome');
        $offerRepository = $em->getRepository('FDOfferBundle:Offer');
        $strategyRepository = $em->getRepository('FDBetBundle:Strategy');
        $betRepository = $em->getRepository('FDBetBundle:Bet');
        $teamRepository = $em->getRepository('FDTeamBundle:Team');
        $marketResultRepository = $em->getRepository('FDResultBundle:MarketResult');


        $strategies = $strategyRepository->findBy(array('label' => '2LastVictoryHigherRankHome2LastDefeatAway'));

        $bets = $betRepository->findBy(array('strategy' => $strategies[0]));
        $maxOutcome = $bets[sizeof($bets)-1]->getOutcome();

        $offerIds = $outcomeRepository->findAllOfferDistinctHtId($maxOutcome->getId());
        foreach ($offerIds as $offerId) {
            $offer = $offerRepository->find($offerId[1]);
            $date = $offer->getDate();
            $labelSplit = explode('-', $offer->getLabel());
            $competitionId = $offer->getCompetitionId();
            $homeTeamLabel = $labelSplit[0];
            $awayTeamLabel = $labelSplit[1];

            if(substr($homeTeamLabel, -1)== ' ')
            {
                $homeTeamLabel = substr($homeTeamLabel, 0, -1);
            }
            if(substr($awayTeamLabel, -1)== ' ')
            {
                $awayTeamLabel = substr($awayTeamLabel, 0, -1);
            }

            $homeTeam = $teamRepository->findBy(array('label' => $homeTeamLabel, 'competitionId' => $competitionId));
            $awayTeam = $teamRepository->findBy(array('label' => $awayTeamLabel, 'competitionId' => $competitionId));
            if(!empty($homeTeam) && !empty($awayTeam)) {
                $teams = [$homeTeam, $awayTeam];

                $nbHomeMarketResult = $marketResultRepository->findByLabelAndCompetitionIdAndDate($homeTeam[0]->getLabel(), $homeTeam[0]->getCompetitionId(), $date);
                $nbAwayMarketResult = $marketResultRepository->findByLabelAndCompetitionIdAndDate($awayTeam[0]->getLabel(), $awayTeam[0]->getCompetitionId(), $date);
                if(count($nbHomeMarketResult) > count($nbAwayMarketResult))
                {
                    $nbMarketResult = count($nbAwayMarketResult);
                }
                else
                {
                    $nbMarketResult = count($nbHomeMarketResult);
                }

                $cptPersist = 0;

                foreach ($teams as $teamItem) {
                    $team = $teamItem[0];
                    $team->setPoints(0);
                    $team->setSerie('');
                    $teamLabel = $team->getLabel();
                    $marketResultsNoCut = $marketResultRepository->findByLabelAndCompetitionIdAndDate($teamLabel, $team->getCompetitionId(), $date);
                    $marketResults = array_slice($marketResultsNoCut, 0, $nbMarketResult);
                    foreach ($marketResults as $marketResult) {
                        $result = $marketResult->getResult();
                        $resultLabel = $result->getLabel();
                        $resultLabelSplit = explode('-', $resultLabel);
                        $resultat = $marketResult->getResultat();
                        if(substr($resultLabelSplit[0], -1)== ' ')
                        {
                            $resultLabelSplit[0] = substr($resultLabelSplit[0], 0, -1);
                        }
                        if ($resultLabelSplit[0] == $teamLabel) {
                            switch ($resultat) {
                                case '1':
                                    $team->setPoints($team->getPoints() + 3);
                                    $team->setSerie( $team->getSerie().'V');
                                    break;
                                case 'N':
                                    $team->setPoints($team->getPoints() + 1);
                                    $team->setSerie($team->getSerie().'N');
                                    break;
                                case '2':
                                    $team->setSerie($team->getSerie().'D');
                                    break;
                            }
                        } else {
                            switch ($resultat) {
                                case '1':
                                    $team->setSerie($team->getSerie().'D');
                                    break;
                                case 'N':
                                    $team->setPoints($team->getPoints() + 1);
                                    $team->setSerie($team->getSerie().'N');
                                    break;
                                case '2':
                                    $team->setPoints($team->getPoints() + 3);
                                    $team->setSerie($team->getSerie().'V');
                                    break;
                            }
                        }
                    }
                    $em->persist($team);
                    $cptPersist++;

                    if ($cptPersist == 1000) {
                        $em->flush();
                        $cptPersist = 0;
                    }
                }

                if ($cptPersist > 0) {
                    $em->flush();
                }
            }
            $outcomes = $outcomeRepository->findBy(array('offer' => $offer));

            $resultQuery = $betRepository->findBy(array('outcome' => $outcomes[0], 'strategy' => $strategies[0]));
            if (empty($resultQuery)) {


                $resultQueryHomeTeam = $teamRepository->findBy(array('label' => $labelSplit[0], 'competitionId' => $competitionId));
                $resultQueryAwayTeam = $teamRepository->findBy(array('label' => $labelSplit[1], 'competitionId' => $competitionId));
                if (!empty($resultQueryHomeTeam) && !empty($resultQueryAwayTeam)) {
                    $homeTeam = $resultQueryHomeTeam[0];
                    $awayTeam = $resultQueryAwayTeam[0];
                    $homeSerie = $homeTeam->getSerie();
                    $awaySerie = $awayTeam->getSerie();
                    if (strlen($awaySerie) > 1 && strlen($homeSerie) > 1) {
                        if ($homeSerie[1] == 'V' && $homeSerie[0] == 'V' && $awaySerie[1] == 'D' && $awaySerie[0] == 'D' && ($homeTeam->getPoints() > $awayTeam->getPoints())) {
                            $bet = new Bet();
                            $bet->setStrategy($strategies[0]);
                            $bet->setOutcome($outcomes[0]);
                            $em->persist($bet);
                            $em->flush();


                        }
                    }
                }
            }
        }

        $strategies[0]->setWaiting(true);
        $em->persist($strategies[0]);
        $em->flush();

        $strategies = $strategyRepository->findAll();


        return $this->render('FDBetBundle:Bet:index.html.twig', array("strategies" => $strategies));

    }






    public function strategy3LastVictoryHigherRankHomeLastDefeatAwayAction()
    {
        ini_set('memory_limit', '2048M');
        ini_set('max_execution_time', 6000);


        $em = $this->getDoctrine()->getManager('default');
        $outcomeRepository = $em->getRepository('FDOfferBundle:Outcome');
        $offerRepository = $em->getRepository('FDOfferBundle:Offer');
        $strategyRepository = $em->getRepository('FDBetBundle:Strategy');
        $betRepository = $em->getRepository('FDBetBundle:Bet');
        $teamRepository = $em->getRepository('FDTeamBundle:Team');
        $marketResultRepository = $em->getRepository('FDResultBundle:MarketResult');


        $strategies = $strategyRepository->findBy(array('label' => '3LastVictoryHigherRankHomeLastDefeatAway'));

        $bets = $betRepository->findBy(array('strategy' => $strategies[0]));
        $maxOutcome = $bets[sizeof($bets)-1]->getOutcome();

        $offerIds = $outcomeRepository->findAllOfferDistinctHtId($maxOutcome->getId());
        foreach ($offerIds as $offerId) {
            $offer = $offerRepository->find($offerId[1]);
            $date = $offer->getDate();
            $labelSplit = explode('-', $offer->getLabel());
            $competitionId = $offer->getCompetitionId();
            $homeTeamLabel = $labelSplit[0];
            $awayTeamLabel = $labelSplit[1];

            if(substr($homeTeamLabel, -1)== ' ')
            {
                $homeTeamLabel = substr($homeTeamLabel, 0, -1);
            }
            if(substr($awayTeamLabel, -1)== ' ')
            {
                $awayTeamLabel = substr($awayTeamLabel, 0, -1);
            }

            $homeTeam = $teamRepository->findBy(array('label' => $homeTeamLabel, 'competitionId' => $competitionId));
            $awayTeam = $teamRepository->findBy(array('label' => $awayTeamLabel, 'competitionId' => $competitionId));
            if(!empty($homeTeam) && !empty($awayTeam)) {
                $teams = [$homeTeam, $awayTeam];

                $nbHomeMarketResult = $marketResultRepository->findByLabelAndCompetitionIdAndDate($homeTeam[0]->getLabel(), $homeTeam[0]->getCompetitionId(), $date);
                $nbAwayMarketResult = $marketResultRepository->findByLabelAndCompetitionIdAndDate($awayTeam[0]->getLabel(), $awayTeam[0]->getCompetitionId(), $date);
                if(count($nbHomeMarketResult) > count($nbAwayMarketResult))
                {
                    $nbMarketResult = count($nbAwayMarketResult);
                }
                else
                {
                    $nbMarketResult = count($nbHomeMarketResult);
                }

                $cptPersist = 0;

                foreach ($teams as $teamItem) {
                    $team = $teamItem[0];
                    $team->setPoints(0);
                    $team->setSerie('');
                    $teamLabel = $team->getLabel();
                    $marketResultsNoCut = $marketResultRepository->findByLabelAndCompetitionIdAndDate($teamLabel, $team->getCompetitionId(), $date);
                    $marketResults = array_slice($marketResultsNoCut, 0, $nbMarketResult);
                    foreach ($marketResults as $marketResult) {
                        $result = $marketResult->getResult();
                        $resultLabel = $result->getLabel();
                        $resultLabelSplit = explode('-', $resultLabel);
                        $resultat = $marketResult->getResultat();
                        if(substr($resultLabelSplit[0], -1)== ' ')
                        {
                            $resultLabelSplit[0] = substr($resultLabelSplit[0], 0, -1);
                        }
                        if ($resultLabelSplit[0] == $teamLabel) {
                            switch ($resultat) {
                                case '1':
                                    $team->setPoints($team->getPoints() + 3);
                                    $team->setSerie( $team->getSerie().'V');
                                    break;
                                case 'N':
                                    $team->setPoints($team->getPoints() + 1);
                                    $team->setSerie($team->getSerie().'N');
                                    break;
                                case '2':
                                    $team->setSerie($team->getSerie().'D');
                                    break;
                            }
                        } else {
                            switch ($resultat) {
                                case '1':
                                    $team->setSerie($team->getSerie().'D');
                                    break;
                                case 'N':
                                    $team->setPoints($team->getPoints() + 1);
                                    $team->setSerie($team->getSerie().'N');
                                    break;
                                case '2':
                                    $team->setPoints($team->getPoints() + 3);
                                    $team->setSerie($team->getSerie().'V');
                                    break;
                            }
                        }
                    }
                    $em->persist($team);
                    $cptPersist++;

                    if ($cptPersist == 1000) {
                        $em->flush();
                        $cptPersist = 0;
                    }
                }

                if ($cptPersist > 0) {
                    $em->flush();
                }
            }
            $outcomes = $outcomeRepository->findBy(array('offer' => $offer));

            $resultQuery = $betRepository->findBy(array('outcome' => $outcomes[0], 'strategy' => $strategies[0]));
            if (empty($resultQuery)) {


                $resultQueryHomeTeam = $teamRepository->findBy(array('label' => $labelSplit[0], 'competitionId' => $competitionId));
                $resultQueryAwayTeam = $teamRepository->findBy(array('label' => $labelSplit[1], 'competitionId' => $competitionId));
                if (!empty($resultQueryHomeTeam) && !empty($resultQueryAwayTeam)) {
                    $homeTeam = $resultQueryHomeTeam[0];
                    $awayTeam = $resultQueryAwayTeam[0];
                    $homeSerie = $homeTeam->getSerie();
                    $awaySerie = $awayTeam->getSerie();
                    if (strlen($awaySerie) > 0 && strlen($homeSerie) > 2) {
                        if ($homeSerie[1] == 'V' && $homeSerie[0] == 'V' && $homeSerie[2] == 'V' && $awaySerie[0] == 'D' && ($homeTeam->getPoints() > $awayTeam->getPoints())) {
                            $bet = new Bet();
                            $bet->setStrategy($strategies[0]);
                            $bet->setOutcome($outcomes[0]);
                            $em->persist($bet);
                            $em->flush();


                        }
                    }
                }
            }
        }

        $strategies[0]->setWaiting(true);
        $em->persist($strategies[0]);
        $em->flush();

        $strategies = $strategyRepository->findAll();


        return $this->render('FDBetBundle:Bet:index.html.twig', array("strategies" => $strategies));

    }





    public function strategy3LastVictoryHigherRankHome2LastDefeatAwayAction()
    {
        ini_set('memory_limit', '2048M');
        ini_set('max_execution_time', 6000);


        $em = $this->getDoctrine()->getManager('default');
        $outcomeRepository = $em->getRepository('FDOfferBundle:Outcome');
        $offerRepository = $em->getRepository('FDOfferBundle:Offer');
        $strategyRepository = $em->getRepository('FDBetBundle:Strategy');
        $betRepository = $em->getRepository('FDBetBundle:Bet');
        $teamRepository = $em->getRepository('FDTeamBundle:Team');
        $marketResultRepository = $em->getRepository('FDResultBundle:MarketResult');


        $strategies = $strategyRepository->findBy(array('label' => '3LastVictoryHigherRankHome2LastDefeatAway'));

        $bets = $betRepository->findBy(array('strategy' => $strategies[0]));
        $maxOutcome = $bets[sizeof($bets)-1]->getOutcome();

        $offerIds = $outcomeRepository->findAllOfferDistinctHtId($maxOutcome->getId());
        foreach ($offerIds as $offerId) {
            $offer = $offerRepository->find($offerId[1]);
            $date = $offer->getDate();
            $labelSplit = explode('-', $offer->getLabel());
            $competitionId = $offer->getCompetitionId();
            $homeTeamLabel = $labelSplit[0];
            $awayTeamLabel = $labelSplit[1];

            if(substr($homeTeamLabel, -1)== ' ')
            {
                $homeTeamLabel = substr($homeTeamLabel, 0, -1);
            }
            if(substr($awayTeamLabel, -1)== ' ')
            {
                $awayTeamLabel = substr($awayTeamLabel, 0, -1);
            }

            $homeTeam = $teamRepository->findBy(array('label' => $homeTeamLabel, 'competitionId' => $competitionId));
            $awayTeam = $teamRepository->findBy(array('label' => $awayTeamLabel, 'competitionId' => $competitionId));
            if(!empty($homeTeam) && !empty($awayTeam)) {
                $teams = [$homeTeam, $awayTeam];

                $nbHomeMarketResult = $marketResultRepository->findByLabelAndCompetitionIdAndDate($homeTeam[0]->getLabel(), $homeTeam[0]->getCompetitionId(), $date);
                $nbAwayMarketResult = $marketResultRepository->findByLabelAndCompetitionIdAndDate($awayTeam[0]->getLabel(), $awayTeam[0]->getCompetitionId(), $date);
                if(count($nbHomeMarketResult) > count($nbAwayMarketResult))
                {
                    $nbMarketResult = count($nbAwayMarketResult);
                }
                else
                {
                    $nbMarketResult = count($nbHomeMarketResult);
                }

                $cptPersist = 0;

                foreach ($teams as $teamItem) {
                    $team = $teamItem[0];
                    $team->setPoints(0);
                    $team->setSerie('');
                    $teamLabel = $team->getLabel();
                    $marketResultsNoCut = $marketResultRepository->findByLabelAndCompetitionIdAndDate($teamLabel, $team->getCompetitionId(), $date);
                    $marketResults = array_slice($marketResultsNoCut, 0, $nbMarketResult);
                    foreach ($marketResults as $marketResult) {
                        $result = $marketResult->getResult();
                        $resultLabel = $result->getLabel();
                        $resultLabelSplit = explode('-', $resultLabel);
                        $resultat = $marketResult->getResultat();
                        if(substr($resultLabelSplit[0], -1)== ' ')
                        {
                            $resultLabelSplit[0] = substr($resultLabelSplit[0], 0, -1);
                        }
                        if ($resultLabelSplit[0] == $teamLabel) {
                            switch ($resultat) {
                                case '1':
                                    $team->setPoints($team->getPoints() + 3);
                                    $team->setSerie( $team->getSerie().'V');
                                    break;
                                case 'N':
                                    $team->setPoints($team->getPoints() + 1);
                                    $team->setSerie($team->getSerie().'N');
                                    break;
                                case '2':
                                    $team->setSerie($team->getSerie().'D');
                                    break;
                            }
                        } else {
                            switch ($resultat) {
                                case '1':
                                    $team->setSerie($team->getSerie().'D');
                                    break;
                                case 'N':
                                    $team->setPoints($team->getPoints() + 1);
                                    $team->setSerie($team->getSerie().'N');
                                    break;
                                case '2':
                                    $team->setPoints($team->getPoints() + 3);
                                    $team->setSerie($team->getSerie().'V');
                                    break;
                            }
                        }
                    }
                    $em->persist($team);
                    $cptPersist++;

                    if ($cptPersist == 1000) {
                        $em->flush();
                        $cptPersist = 0;
                    }
                }

                if ($cptPersist > 0) {
                    $em->flush();
                }
            }
            $outcomes = $outcomeRepository->findBy(array('offer' => $offer));

            $resultQuery = $betRepository->findBy(array('outcome' => $outcomes[0], 'strategy' => $strategies[0]));
            if (empty($resultQuery)) {


                $resultQueryHomeTeam = $teamRepository->findBy(array('label' => $labelSplit[0], 'competitionId' => $competitionId));
                $resultQueryAwayTeam = $teamRepository->findBy(array('label' => $labelSplit[1], 'competitionId' => $competitionId));
                if (!empty($resultQueryHomeTeam) && !empty($resultQueryAwayTeam)) {
                    $homeTeam = $resultQueryHomeTeam[0];
                    $awayTeam = $resultQueryAwayTeam[0];
                    $homeSerie = $homeTeam->getSerie();
                    $awaySerie = $awayTeam->getSerie();
                    if (strlen($awaySerie) > 1 && strlen($homeSerie) > 2) {
                        if ($homeSerie[1] == 'V' && $homeSerie[0] == 'V' && $homeSerie[2] == 'V' && $awaySerie[1] == 'D' && $awaySerie[0] == 'D' && ($homeTeam->getPoints() > $awayTeam->getPoints())) {
                            $bet = new Bet();
                            $bet->setStrategy($strategies[0]);
                            $bet->setOutcome($outcomes[0]);
                            $em->persist($bet);
                            $em->flush();


                        }
                    }
                }
            }
        }

        $strategies[0]->setWaiting(true);
        $em->persist($strategies[0]);
        $em->flush();

        $strategies = $strategyRepository->findAll();


        return $this->render('FDBetBundle:Bet:index.html.twig', array("strategies" => $strategies));

    }






    public function strategyLastVictoryHigherRankHome3LastDefeatAwayAction()
    {
        ini_set('memory_limit', '2048M');
        ini_set('max_execution_time', 6000);


        $em = $this->getDoctrine()->getManager('default');
        $outcomeRepository = $em->getRepository('FDOfferBundle:Outcome');
        $offerRepository = $em->getRepository('FDOfferBundle:Offer');
        $strategyRepository = $em->getRepository('FDBetBundle:Strategy');
        $betRepository = $em->getRepository('FDBetBundle:Bet');
        $teamRepository = $em->getRepository('FDTeamBundle:Team');
        $marketResultRepository = $em->getRepository('FDResultBundle:MarketResult');


        $strategies = $strategyRepository->findBy(array('label' => 'LastVictoryHigherRankHome3LastDefeatAway'));

        $bets = $betRepository->findBy(array('strategy' => $strategies[0]));
        $maxOutcome = $bets[sizeof($bets)-1]->getOutcome();

        $offerIds = $outcomeRepository->findAllOfferDistinctHtId($maxOutcome->getId());
        foreach ($offerIds as $offerId) {
            $offer = $offerRepository->find($offerId[1]);
            $date = $offer->getDate();
            $labelSplit = explode('-', $offer->getLabel());
            $competitionId = $offer->getCompetitionId();
            $homeTeamLabel = $labelSplit[0];
            $awayTeamLabel = $labelSplit[1];

            if(substr($homeTeamLabel, -1)== ' ')
            {
                $homeTeamLabel = substr($homeTeamLabel, 0, -1);
            }
            if(substr($awayTeamLabel, -1)== ' ')
            {
                $awayTeamLabel = substr($awayTeamLabel, 0, -1);
            }

            $homeTeam = $teamRepository->findBy(array('label' => $homeTeamLabel, 'competitionId' => $competitionId));
            $awayTeam = $teamRepository->findBy(array('label' => $awayTeamLabel, 'competitionId' => $competitionId));
            if(!empty($homeTeam) && !empty($awayTeam)) {
                $teams = [$homeTeam, $awayTeam];

                $nbHomeMarketResult = $marketResultRepository->findByLabelAndCompetitionIdAndDate($homeTeam[0]->getLabel(), $homeTeam[0]->getCompetitionId(), $date);
                $nbAwayMarketResult = $marketResultRepository->findByLabelAndCompetitionIdAndDate($awayTeam[0]->getLabel(), $awayTeam[0]->getCompetitionId(), $date);
                if(count($nbHomeMarketResult) > count($nbAwayMarketResult))
                {
                    $nbMarketResult = count($nbAwayMarketResult);
                }
                else
                {
                    $nbMarketResult = count($nbHomeMarketResult);
                }

                $cptPersist = 0;

                foreach ($teams as $teamItem) {
                    $team = $teamItem[0];
                    $team->setPoints(0);
                    $team->setSerie('');
                    $teamLabel = $team->getLabel();
                    $marketResultsNoCut = $marketResultRepository->findByLabelAndCompetitionIdAndDate($teamLabel, $team->getCompetitionId(), $date);
                    $marketResults = array_slice($marketResultsNoCut, 0, $nbMarketResult);
                    foreach ($marketResults as $marketResult) {
                        $result = $marketResult->getResult();
                        $resultLabel = $result->getLabel();
                        $resultLabelSplit = explode('-', $resultLabel);
                        $resultat = $marketResult->getResultat();
                        if(substr($resultLabelSplit[0], -1)== ' ')
                        {
                            $resultLabelSplit[0] = substr($resultLabelSplit[0], 0, -1);
                        }
                        if ($resultLabelSplit[0] == $teamLabel) {
                            switch ($resultat) {
                                case '1':
                                    $team->setPoints($team->getPoints() + 3);
                                    $team->setSerie( $team->getSerie().'V');
                                    break;
                                case 'N':
                                    $team->setPoints($team->getPoints() + 1);
                                    $team->setSerie($team->getSerie().'N');
                                    break;
                                case '2':
                                    $team->setSerie($team->getSerie().'D');
                                    break;
                            }
                        } else {
                            switch ($resultat) {
                                case '1':
                                    $team->setSerie($team->getSerie().'D');
                                    break;
                                case 'N':
                                    $team->setPoints($team->getPoints() + 1);
                                    $team->setSerie($team->getSerie().'N');
                                    break;
                                case '2':
                                    $team->setPoints($team->getPoints() + 3);
                                    $team->setSerie($team->getSerie().'V');
                                    break;
                            }
                        }
                    }
                    $em->persist($team);
                    $cptPersist++;

                    if ($cptPersist == 1000) {
                        $em->flush();
                        $cptPersist = 0;
                    }
                }

                if ($cptPersist > 0) {
                    $em->flush();
                }
            }
            $outcomes = $outcomeRepository->findBy(array('offer' => $offer));

            $resultQuery = $betRepository->findBy(array('outcome' => $outcomes[0], 'strategy' => $strategies[0]));
            if (empty($resultQuery)) {


                $resultQueryHomeTeam = $teamRepository->findBy(array('label' => $labelSplit[0], 'competitionId' => $competitionId));
                $resultQueryAwayTeam = $teamRepository->findBy(array('label' => $labelSplit[1], 'competitionId' => $competitionId));
                if (!empty($resultQueryHomeTeam) && !empty($resultQueryAwayTeam)) {
                    $homeTeam = $resultQueryHomeTeam[0];
                    $awayTeam = $resultQueryAwayTeam[0];
                    $homeSerie = $homeTeam->getSerie();
                    $awaySerie = $awayTeam->getSerie();
                    if (strlen($awaySerie) > 2 && strlen($homeSerie) > 0) {
                        if ($homeSerie[0] == 'V' && $awaySerie[1] == 'D' && $awaySerie[0] == 'D' && $awaySerie[2]== 'D' && ($homeTeam->getPoints() > $awayTeam->getPoints())) {
                            $bet = new Bet();
                            $bet->setStrategy($strategies[0]);
                            $bet->setOutcome($outcomes[0]);
                            $em->persist($bet);
                            $em->flush();


                        }
                    }
                }
            }
        }

        $strategies[0]->setWaiting(true);
        $em->persist($strategies[0]);
        $em->flush();

        $strategies = $strategyRepository->findAll();


        return $this->render('FDBetBundle:Bet:index.html.twig', array("strategies" => $strategies));

    }







    public function strategy2LastVictoryHigherRankHome3LastDefeatAwayAction()
    {
        ini_set('memory_limit', '2048M');
        ini_set('max_execution_time', 6000);


        $em = $this->getDoctrine()->getManager('default');
        $outcomeRepository = $em->getRepository('FDOfferBundle:Outcome');
        $offerRepository = $em->getRepository('FDOfferBundle:Offer');
        $strategyRepository = $em->getRepository('FDBetBundle:Strategy');
        $betRepository = $em->getRepository('FDBetBundle:Bet');
        $teamRepository = $em->getRepository('FDTeamBundle:Team');
        $marketResultRepository = $em->getRepository('FDResultBundle:MarketResult');


        $strategies = $strategyRepository->findBy(array('label' => '2LastVictoryHigherRankHome3LastDefeatAway'));

        $bets = $betRepository->findBy(array('strategy' => $strategies[0]));
        $maxOutcome = $bets[sizeof($bets)-1]->getOutcome();

        $offerIds = $outcomeRepository->findAllOfferDistinctHtId($maxOutcome->getId());
        foreach ($offerIds as $offerId) {
            $offer = $offerRepository->find($offerId[1]);
            $date = $offer->getDate();
            $labelSplit = explode('-', $offer->getLabel());
            $competitionId = $offer->getCompetitionId();
            $homeTeamLabel = $labelSplit[0];
            $awayTeamLabel = $labelSplit[1];

            if(substr($homeTeamLabel, -1)== ' ')
            {
                $homeTeamLabel = substr($homeTeamLabel, 0, -1);
            }
            if(substr($awayTeamLabel, -1)== ' ')
            {
                $awayTeamLabel = substr($awayTeamLabel, 0, -1);
            }

            $homeTeam = $teamRepository->findBy(array('label' => $homeTeamLabel, 'competitionId' => $competitionId));
            $awayTeam = $teamRepository->findBy(array('label' => $awayTeamLabel, 'competitionId' => $competitionId));
            if(!empty($homeTeam) && !empty($awayTeam)) {
                $teams = [$homeTeam, $awayTeam];

                $nbHomeMarketResult = $marketResultRepository->findByLabelAndCompetitionIdAndDate($homeTeam[0]->getLabel(), $homeTeam[0]->getCompetitionId(), $date);
                $nbAwayMarketResult = $marketResultRepository->findByLabelAndCompetitionIdAndDate($awayTeam[0]->getLabel(), $awayTeam[0]->getCompetitionId(), $date);
                if(count($nbHomeMarketResult) > count($nbAwayMarketResult))
                {
                    $nbMarketResult = count($nbAwayMarketResult);
                }
                else
                {
                    $nbMarketResult = count($nbHomeMarketResult);
                }

                $cptPersist = 0;

                foreach ($teams as $teamItem) {
                    $team = $teamItem[0];
                    $team->setPoints(0);
                    $team->setSerie('');
                    $teamLabel = $team->getLabel();
                    $marketResultsNoCut = $marketResultRepository->findByLabelAndCompetitionIdAndDate($teamLabel, $team->getCompetitionId(), $date);
                    $marketResults = array_slice($marketResultsNoCut, 0, $nbMarketResult);
                    foreach ($marketResults as $marketResult) {
                        $result = $marketResult->getResult();
                        $resultLabel = $result->getLabel();
                        $resultLabelSplit = explode('-', $resultLabel);
                        $resultat = $marketResult->getResultat();
                        if(substr($resultLabelSplit[0], -1)== ' ')
                        {
                            $resultLabelSplit[0] = substr($resultLabelSplit[0], 0, -1);
                        }
                        if ($resultLabelSplit[0] == $teamLabel) {
                            switch ($resultat) {
                                case '1':
                                    $team->setPoints($team->getPoints() + 3);
                                    $team->setSerie( $team->getSerie().'V');
                                    break;
                                case 'N':
                                    $team->setPoints($team->getPoints() + 1);
                                    $team->setSerie($team->getSerie().'N');
                                    break;
                                case '2':
                                    $team->setSerie($team->getSerie().'D');
                                    break;
                            }
                        } else {
                            switch ($resultat) {
                                case '1':
                                    $team->setSerie($team->getSerie().'D');
                                    break;
                                case 'N':
                                    $team->setPoints($team->getPoints() + 1);
                                    $team->setSerie($team->getSerie().'N');
                                    break;
                                case '2':
                                    $team->setPoints($team->getPoints() + 3);
                                    $team->setSerie($team->getSerie().'V');
                                    break;
                            }
                        }
                    }
                    $em->persist($team);
                    $cptPersist++;

                    if ($cptPersist == 1000) {
                        $em->flush();
                        $cptPersist = 0;
                    }
                }

                if ($cptPersist > 0) {
                    $em->flush();
                }
            }
            $outcomes = $outcomeRepository->findBy(array('offer' => $offer));

            $resultQuery = $betRepository->findBy(array('outcome' => $outcomes[0], 'strategy' => $strategies[0]));
            if (empty($resultQuery)) {


                $resultQueryHomeTeam = $teamRepository->findBy(array('label' => $labelSplit[0], 'competitionId' => $competitionId));
                $resultQueryAwayTeam = $teamRepository->findBy(array('label' => $labelSplit[1], 'competitionId' => $competitionId));
                if (!empty($resultQueryHomeTeam) && !empty($resultQueryAwayTeam)) {
                    $homeTeam = $resultQueryHomeTeam[0];
                    $awayTeam = $resultQueryAwayTeam[0];
                    $homeSerie = $homeTeam->getSerie();
                    $awaySerie = $awayTeam->getSerie();
                    if (strlen($awaySerie) > 2 && strlen($homeSerie) > 1) {
                        if ($homeSerie[0] == 'V' && $homeSerie[1] == 'V' && $awaySerie[1] == 'D' && $awaySerie[0] == 'D' && $awaySerie[2]== 'D' && ($homeTeam->getPoints() > $awayTeam->getPoints())) {
                            $bet = new Bet();
                            $bet->setStrategy($strategies[0]);
                            $bet->setOutcome($outcomes[0]);
                            $em->persist($bet);
                            $em->flush();


                        }
                    }
                }
            }
        }

        $strategies[0]->setWaiting(true);
        $em->persist($strategies[0]);
        $em->flush();

        $strategies = $strategyRepository->findAll();


        return $this->render('FDBetBundle:Bet:index.html.twig', array("strategies" => $strategies));

    }





    public function strategy3LastVictoryHigherRankHome3LastDefeatAwayAction()
    {
        ini_set('memory_limit', '2048M');
        ini_set('max_execution_time', 6000);


        $em = $this->getDoctrine()->getManager('default');
        $outcomeRepository = $em->getRepository('FDOfferBundle:Outcome');
        $offerRepository = $em->getRepository('FDOfferBundle:Offer');
        $strategyRepository = $em->getRepository('FDBetBundle:Strategy');
        $betRepository = $em->getRepository('FDBetBundle:Bet');
        $teamRepository = $em->getRepository('FDTeamBundle:Team');
        $marketResultRepository = $em->getRepository('FDResultBundle:MarketResult');


        $strategies = $strategyRepository->findBy(array('label' => '3LastVictoryHigherRankHome3LastDefeatAway'));

        $bets = $betRepository->findBy(array('strategy' => $strategies[0]));
        $maxOutcome = $bets[sizeof($bets)-1]->getOutcome();

        $offerIds = $outcomeRepository->findAllOfferDistinctHtId($maxOutcome->getId());
        foreach ($offerIds as $offerId) {
            $offer = $offerRepository->find($offerId[1]);
            $date = $offer->getDate();
            $labelSplit = explode('-', $offer->getLabel());
            $competitionId = $offer->getCompetitionId();
            $homeTeamLabel = $labelSplit[0];
            $awayTeamLabel = $labelSplit[1];

            if(substr($homeTeamLabel, -1)== ' ')
            {
                $homeTeamLabel = substr($homeTeamLabel, 0, -1);
            }
            if(substr($awayTeamLabel, -1)== ' ')
            {
                $awayTeamLabel = substr($awayTeamLabel, 0, -1);
            }

            $homeTeam = $teamRepository->findBy(array('label' => $homeTeamLabel, 'competitionId' => $competitionId));
            $awayTeam = $teamRepository->findBy(array('label' => $awayTeamLabel, 'competitionId' => $competitionId));
            if(!empty($homeTeam) && !empty($awayTeam)) {
                $teams = [$homeTeam, $awayTeam];

                $nbHomeMarketResult = $marketResultRepository->findByLabelAndCompetitionIdAndDate($homeTeam[0]->getLabel(), $homeTeam[0]->getCompetitionId(), $date);
                $nbAwayMarketResult = $marketResultRepository->findByLabelAndCompetitionIdAndDate($awayTeam[0]->getLabel(), $awayTeam[0]->getCompetitionId(), $date);
                if(count($nbHomeMarketResult) > count($nbAwayMarketResult))
                {
                    $nbMarketResult = count($nbAwayMarketResult);
                }
                else
                {
                    $nbMarketResult = count($nbHomeMarketResult);
                }

                $cptPersist = 0;

                foreach ($teams as $teamItem) {
                    $team = $teamItem[0];
                    $team->setPoints(0);
                    $team->setSerie('');
                    $teamLabel = $team->getLabel();
                    $marketResultsNoCut = $marketResultRepository->findByLabelAndCompetitionIdAndDate($teamLabel, $team->getCompetitionId(), $date);
                    $marketResults = array_slice($marketResultsNoCut, 0, $nbMarketResult);
                    foreach ($marketResults as $marketResult) {
                        $result = $marketResult->getResult();
                        $resultLabel = $result->getLabel();
                        $resultLabelSplit = explode('-', $resultLabel);
                        $resultat = $marketResult->getResultat();
                        if(substr($resultLabelSplit[0], -1)== ' ')
                        {
                            $resultLabelSplit[0] = substr($resultLabelSplit[0], 0, -1);
                        }
                        if ($resultLabelSplit[0] == $teamLabel) {
                            switch ($resultat) {
                                case '1':
                                    $team->setPoints($team->getPoints() + 3);
                                    $team->setSerie( $team->getSerie().'V');
                                    break;
                                case 'N':
                                    $team->setPoints($team->getPoints() + 1);
                                    $team->setSerie($team->getSerie().'N');
                                    break;
                                case '2':
                                    $team->setSerie($team->getSerie().'D');
                                    break;
                            }
                        } else {
                            switch ($resultat) {
                                case '1':
                                    $team->setSerie($team->getSerie().'D');
                                    break;
                                case 'N':
                                    $team->setPoints($team->getPoints() + 1);
                                    $team->setSerie($team->getSerie().'N');
                                    break;
                                case '2':
                                    $team->setPoints($team->getPoints() + 3);
                                    $team->setSerie($team->getSerie().'V');
                                    break;
                            }
                        }
                    }
                    $em->persist($team);
                    $cptPersist++;

                    if ($cptPersist == 1000) {
                        $em->flush();
                        $cptPersist = 0;
                    }
                }

                if ($cptPersist > 0) {
                    $em->flush();
                }
            }
            $outcomes = $outcomeRepository->findBy(array('offer' => $offer));

            $resultQuery = $betRepository->findBy(array('outcome' => $outcomes[0], 'strategy' => $strategies[0]));
            if (empty($resultQuery)) {


                $resultQueryHomeTeam = $teamRepository->findBy(array('label' => $labelSplit[0], 'competitionId' => $competitionId));
                $resultQueryAwayTeam = $teamRepository->findBy(array('label' => $labelSplit[1], 'competitionId' => $competitionId));
                if (!empty($resultQueryHomeTeam) && !empty($resultQueryAwayTeam)) {
                    $homeTeam = $resultQueryHomeTeam[0];
                    $awayTeam = $resultQueryAwayTeam[0];
                    $homeSerie = $homeTeam->getSerie();
                    $awaySerie = $awayTeam->getSerie();
                    if (strlen($awaySerie) > 2 && strlen($homeSerie) > 2) {
                        if ($homeSerie[2] == 'V' && $homeSerie[0] == 'V' && $homeSerie[1] == 'V' && $awaySerie[1] == 'D' && $awaySerie[0] == 'D' && $awaySerie[2]== 'D' && ($homeTeam->getPoints() > $awayTeam->getPoints())) {
                            $bet = new Bet();
                            $bet->setStrategy($strategies[0]);
                            $bet->setOutcome($outcomes[0]);
                            $em->persist($bet);
                            $em->flush();


                        }
                    }
                }
            }
        }

        $strategies[0]->setWaiting(true);
        $em->persist($strategies[0]);
        $em->flush();

        $strategies = $strategyRepository->findAll();


        return $this->render('FDBetBundle:Bet:index.html.twig', array("strategies" => $strategies));

    }



















































    public function strategyLastVictoryHigherRankAwayLastDefeatHomeAction()
    {
        ini_set('memory_limit', '2048M');
        ini_set('max_execution_time', 6000);


        $em = $this->getDoctrine()->getManager('default');
        $outcomeRepository = $em->getRepository('FDOfferBundle:Outcome');
        $offerRepository = $em->getRepository('FDOfferBundle:Offer');
        $strategyRepository = $em->getRepository('FDBetBundle:Strategy');
        $betRepository = $em->getRepository('FDBetBundle:Bet');
        $teamRepository = $em->getRepository('FDTeamBundle:Team');
        $marketResultRepository = $em->getRepository('FDResultBundle:MarketResult');

        $strategies = $strategyRepository->findBy(array('label' => 'LastVictoryHigherRankAwayLastDefeatHome'));

        $bets = $betRepository->findBy(array('strategy' => $strategies[0]));
        $maxOutcome = $bets[sizeof($bets)-1]->getOutcome();

        $offerIds = $outcomeRepository->findAllOfferDistinctHtId($maxOutcome->getId());
        foreach ($offerIds as $offerId) {
            $offer = $offerRepository->find($offerId[1]);
            $date = $offer->getDate();
            $labelSplit = explode('-', $offer->getLabel());
            $competitionId = $offer->getCompetitionId();
            $homeTeamLabel = $labelSplit[0];
            $awayTeamLabel = $labelSplit[1];

            if(substr($homeTeamLabel, -1)== ' ')
            {
                $homeTeamLabel = substr($homeTeamLabel, 0, -1);
            }
            if(substr($awayTeamLabel, -1)== ' ')
            {
                $awayTeamLabel = substr($awayTeamLabel, 0, -1);
            }

            $homeTeam = $teamRepository->findBy(array('label' => $homeTeamLabel, 'competitionId' => $competitionId));
            $awayTeam = $teamRepository->findBy(array('label' => $awayTeamLabel, 'competitionId' => $competitionId));
            if(!empty($homeTeam) && !empty($awayTeam)) {
                $teams = [$homeTeam, $awayTeam];

                $nbHomeMarketResult = $marketResultRepository->findByLabelAndCompetitionIdAndDate($homeTeam[0]->getLabel(), $homeTeam[0]->getCompetitionId(), $date);
                $nbAwayMarketResult = $marketResultRepository->findByLabelAndCompetitionIdAndDate($awayTeam[0]->getLabel(), $awayTeam[0]->getCompetitionId(), $date);
                if(count($nbHomeMarketResult) > count($nbAwayMarketResult))
                {
                    $nbMarketResult = count($nbAwayMarketResult);
                }
                else
                {
                    $nbMarketResult = count($nbHomeMarketResult);
                }

                $cptPersist = 0;

                foreach ($teams as $teamItem) {
                    $team = $teamItem[0];
                    $team->setPoints(0);
                    $team->setSerie('');
                    $teamLabel = $team->getLabel();
                    $marketResultsNoCut = $marketResultRepository->findByLabelAndCompetitionIdAndDate($teamLabel, $team->getCompetitionId(), $date);
                    $marketResults = array_slice($marketResultsNoCut, 0, $nbMarketResult);
                    foreach ($marketResults as $marketResult) {
                        $result = $marketResult->getResult();
                        $resultLabel = $result->getLabel();
                        $resultLabelSplit = explode('-', $resultLabel);
                        $resultat = $marketResult->getResultat();
                        if(substr($resultLabelSplit[0], -1)== ' ')
                        {
                            $resultLabelSplit[0] = substr($resultLabelSplit[0], 0, -1);
                        }
                        if ($resultLabelSplit[0] == $teamLabel) {
                            switch ($resultat) {
                                case '1':
                                    $team->setPoints($team->getPoints() + 3);
                                    $team->setSerie( $team->getSerie().'V');
                                    break;
                                case 'N':
                                    $team->setPoints($team->getPoints() + 1);
                                    $team->setSerie($team->getSerie().'N');
                                    break;
                                case '2':
                                    $team->setSerie($team->getSerie().'D');
                                    break;
                            }
                        } else {
                            switch ($resultat) {
                                case '1':
                                    $team->setSerie($team->getSerie().'D');
                                    break;
                                case 'N':
                                    $team->setPoints($team->getPoints() + 1);
                                    $team->setSerie($team->getSerie().'N');
                                    break;
                                case '2':
                                    $team->setPoints($team->getPoints() + 3);
                                    $team->setSerie($team->getSerie().'V');
                                    break;
                            }
                        }
                    }
                    $em->persist($team);
                    $cptPersist++;

                    if ($cptPersist == 1000) {
                        $em->flush();
                        $cptPersist = 0;
                    }
                }

                if ($cptPersist > 0) {
                    $em->flush();
                }
            }
            $outcomes = $outcomeRepository->findBy(array('offer' => $offer));
            if(sizeof($outcomes) == 2)
            {
                $outcome = $outcomes[1];
            }
            else
            {
                $outcome = $outcomes[2];
            }

            $resultQuery = $betRepository->findBy(array('outcome' => $outcome, 'strategy' => $strategies[0]));
            if (empty($resultQuery)) {


                $resultQueryHomeTeam = $teamRepository->findBy(array('label' => $labelSplit[0], 'competitionId' => $competitionId));
                $resultQueryAwayTeam = $teamRepository->findBy(array('label' => $labelSplit[1], 'competitionId' => $competitionId));
                if (!empty($resultQueryHomeTeam) && !empty($resultQueryAwayTeam)) {
                    $homeTeam = $resultQueryHomeTeam[0];
                    $awayTeam = $resultQueryAwayTeam[0];
                    $homeSerie = $homeTeam->getSerie();
                    $awaySerie = $awayTeam->getSerie();
                    if (strlen($awaySerie) > 0 && strlen($homeSerie) > 0) {
                        if ($homeSerie[0] == 'D' && $awaySerie[0]== 'V' && ($homeTeam->getPoints() < $awayTeam->getPoints())) {
                            $bet = new Bet();
                            $bet->setStrategy($strategies[0]);
                            $bet->setOutcome($outcome);
                            $em->persist($bet);
                            $em->flush();


                        }
                    }
                }
            }
        }

        $strategies[0]->setWaiting(true);
        $em->persist($strategies[0]);
        $em->flush();

        $strategies = $strategyRepository->findAll();


        return $this->render('FDBetBundle:Bet:index.html.twig', array("strategies" => $strategies));

    }





    public function strategy2LastVictoryHigherRankAwayLastDefeatHomeAction()
    {
        ini_set('memory_limit', '2048M');
        ini_set('max_execution_time', 6000);


        $em = $this->getDoctrine()->getManager('default');
        $outcomeRepository = $em->getRepository('FDOfferBundle:Outcome');
        $offerRepository = $em->getRepository('FDOfferBundle:Offer');
        $strategyRepository = $em->getRepository('FDBetBundle:Strategy');
        $betRepository = $em->getRepository('FDBetBundle:Bet');
        $teamRepository = $em->getRepository('FDTeamBundle:Team');
        $marketResultRepository = $em->getRepository('FDResultBundle:MarketResult');


        $strategies = $strategyRepository->findBy(array('label' => '2LastVictoryHigherRankAwayLastDefeatHome'));

        $bets = $betRepository->findBy(array('strategy' => $strategies[0]));
        $maxOutcome = $bets[sizeof($bets)-1]->getOutcome();

        $offerIds = $outcomeRepository->findAllOfferDistinctHtId($maxOutcome->getId());

        foreach ($offerIds as $offerId) {
            $offer = $offerRepository->find($offerId[1]);
            $date = $offer->getDate();
            $labelSplit = explode('-', $offer->getLabel());
            $competitionId = $offer->getCompetitionId();
            $homeTeamLabel = $labelSplit[0];
            $awayTeamLabel = $labelSplit[1];

            if(substr($homeTeamLabel, -1)== ' ')
            {
                $homeTeamLabel = substr($homeTeamLabel, 0, -1);
            }
            if(substr($awayTeamLabel, -1)== ' ')
            {
                $awayTeamLabel = substr($awayTeamLabel, 0, -1);
            }

            $homeTeam = $teamRepository->findBy(array('label' => $homeTeamLabel, 'competitionId' => $competitionId));
            $awayTeam = $teamRepository->findBy(array('label' => $awayTeamLabel, 'competitionId' => $competitionId));
            if(!empty($homeTeam) && !empty($awayTeam)) {
                $teams = [$homeTeam, $awayTeam];

                $nbHomeMarketResult = $marketResultRepository->findByLabelAndCompetitionIdAndDate($homeTeam[0]->getLabel(), $homeTeam[0]->getCompetitionId(), $date);
                $nbAwayMarketResult = $marketResultRepository->findByLabelAndCompetitionIdAndDate($awayTeam[0]->getLabel(), $awayTeam[0]->getCompetitionId(), $date);
                if(count($nbHomeMarketResult) > count($nbAwayMarketResult))
                {
                    $nbMarketResult = count($nbAwayMarketResult);
                }
                else
                {
                    $nbMarketResult = count($nbHomeMarketResult);
                }

                $cptPersist = 0;

                foreach ($teams as $teamItem) {
                    $team = $teamItem[0];
                    $team->setPoints(0);
                    $team->setSerie('');
                    $teamLabel = $team->getLabel();
                    $marketResultsNoCut = $marketResultRepository->findByLabelAndCompetitionIdAndDate($teamLabel, $team->getCompetitionId(), $date);
                    $marketResults = array_slice($marketResultsNoCut, 0, $nbMarketResult);
                    foreach ($marketResults as $marketResult) {
                        $result = $marketResult->getResult();
                        $resultLabel = $result->getLabel();
                        $resultLabelSplit = explode('-', $resultLabel);
                        $resultat = $marketResult->getResultat();
                        if(substr($resultLabelSplit[0], -1)== ' ')
                        {
                            $resultLabelSplit[0] = substr($resultLabelSplit[0], 0, -1);
                        }
                        if ($resultLabelSplit[0] == $teamLabel) {
                            switch ($resultat) {
                                case '1':
                                    $team->setPoints($team->getPoints() + 3);
                                    $team->setSerie( $team->getSerie().'V');
                                    break;
                                case 'N':
                                    $team->setPoints($team->getPoints() + 1);
                                    $team->setSerie($team->getSerie().'N');
                                    break;
                                case '2':
                                    $team->setSerie($team->getSerie().'D');
                                    break;
                            }
                        } else {
                            switch ($resultat) {
                                case '1':
                                    $team->setSerie($team->getSerie().'D');
                                    break;
                                case 'N':
                                    $team->setPoints($team->getPoints() + 1);
                                    $team->setSerie($team->getSerie().'N');
                                    break;
                                case '2':
                                    $team->setPoints($team->getPoints() + 3);
                                    $team->setSerie($team->getSerie().'V');
                                    break;
                            }
                        }
                    }
                    $em->persist($team);
                    $cptPersist++;

                    if ($cptPersist == 1000) {
                        $em->flush();
                        $cptPersist = 0;
                    }
                }

                if ($cptPersist > 0) {
                    $em->flush();
                }
            }
            $outcomes = $outcomeRepository->findBy(array('offer' => $offer));
            if(sizeof($outcomes) == 2)
            {
                $outcome = $outcomes[1];
            }
            else
            {
                $outcome = $outcomes[2];
            }

            $resultQuery = $betRepository->findBy(array('outcome' => $outcome, 'strategy' => $strategies[0]));
            if (empty($resultQuery)) {


                $resultQueryHomeTeam = $teamRepository->findBy(array('label' => $labelSplit[0], 'competitionId' => $competitionId));
                $resultQueryAwayTeam = $teamRepository->findBy(array('label' => $labelSplit[1], 'competitionId' => $competitionId));
                if (!empty($resultQueryHomeTeam) && !empty($resultQueryAwayTeam)) {
                    $homeTeam = $resultQueryHomeTeam[0];
                    $awayTeam = $resultQueryAwayTeam[0];
                    $homeSerie = $homeTeam->getSerie();
                    $awaySerie = $awayTeam->getSerie();
                    if (strlen($awaySerie) > 1 && strlen($homeSerie) > 0) {
                        if ($homeSerie[0] == 'D' && $awaySerie[0]== 'V' && $awaySerie[1] == 'V' && ($homeTeam->getPoints() < $awayTeam->getPoints())) {
                            $bet = new Bet();
                            $bet->setStrategy($strategies[0]);
                            $bet->setOutcome($outcome);
                            $em->persist($bet);
                            $em->flush();


                        }
                    }
                }
            }
        }

        $strategies[0]->setWaiting(true);
        $em->persist($strategies[0]);
        $em->flush();

        $strategies = $strategyRepository->findAll();


        return $this->render('FDBetBundle:Bet:index.html.twig', array("strategies" => $strategies));

    }









    public function strategyLastVictoryHigherRankAway2LastDefeatHomeAction()
    {
        ini_set('memory_limit', '2048M');
        ini_set('max_execution_time', 6000);


        $em = $this->getDoctrine()->getManager('default');
        $outcomeRepository = $em->getRepository('FDOfferBundle:Outcome');
        $offerRepository = $em->getRepository('FDOfferBundle:Offer');
        $strategyRepository = $em->getRepository('FDBetBundle:Strategy');
        $betRepository = $em->getRepository('FDBetBundle:Bet');
        $teamRepository = $em->getRepository('FDTeamBundle:Team');
        $marketResultRepository = $em->getRepository('FDResultBundle:MarketResult');


        $strategies = $strategyRepository->findBy(array('label' => 'LastVictoryHigherRankAway2LastDefeatHome'));

        $bets = $betRepository->findBy(array('strategy' => $strategies[0]));
        $maxOutcome = $bets[sizeof($bets)-1]->getOutcome();

        $offerIds = $outcomeRepository->findAllOfferDistinctHtId($maxOutcome->getId());
        foreach ($offerIds as $offerId) {
            $offer = $offerRepository->find($offerId[1]);
            $date = $offer->getDate();
            $labelSplit = explode('-', $offer->getLabel());
            $competitionId = $offer->getCompetitionId();
            $homeTeamLabel = $labelSplit[0];
            $awayTeamLabel = $labelSplit[1];

            if(substr($homeTeamLabel, -1)== ' ')
            {
                $homeTeamLabel = substr($homeTeamLabel, 0, -1);
            }
            if(substr($awayTeamLabel, -1)== ' ')
            {
                $awayTeamLabel = substr($awayTeamLabel, 0, -1);
            }

            $homeTeam = $teamRepository->findBy(array('label' => $homeTeamLabel, 'competitionId' => $competitionId));
            $awayTeam = $teamRepository->findBy(array('label' => $awayTeamLabel, 'competitionId' => $competitionId));
            if(!empty($homeTeam) && !empty($awayTeam)) {
                $teams = [$homeTeam, $awayTeam];

                $nbHomeMarketResult = $marketResultRepository->findByLabelAndCompetitionIdAndDate($homeTeam[0]->getLabel(), $homeTeam[0]->getCompetitionId(), $date);
                $nbAwayMarketResult = $marketResultRepository->findByLabelAndCompetitionIdAndDate($awayTeam[0]->getLabel(), $awayTeam[0]->getCompetitionId(), $date);
                if(count($nbHomeMarketResult) > count($nbAwayMarketResult))
                {
                    $nbMarketResult = count($nbAwayMarketResult);
                }
                else
                {
                    $nbMarketResult = count($nbHomeMarketResult);
                }

                $cptPersist = 0;

                foreach ($teams as $teamItem) {
                    $team = $teamItem[0];
                    $team->setPoints(0);
                    $team->setSerie('');
                    $teamLabel = $team->getLabel();
                    $marketResultsNoCut = $marketResultRepository->findByLabelAndCompetitionIdAndDate($teamLabel, $team->getCompetitionId(), $date);
                    $marketResults = array_slice($marketResultsNoCut, 0, $nbMarketResult);
                    foreach ($marketResults as $marketResult) {
                        $result = $marketResult->getResult();
                        $resultLabel = $result->getLabel();
                        $resultLabelSplit = explode('-', $resultLabel);
                        $resultat = $marketResult->getResultat();
                        if(substr($resultLabelSplit[0], -1)== ' ')
                        {
                            $resultLabelSplit[0] = substr($resultLabelSplit[0], 0, -1);
                        }
                        if ($resultLabelSplit[0] == $teamLabel) {
                            switch ($resultat) {
                                case '1':
                                    $team->setPoints($team->getPoints() + 3);
                                    $team->setSerie( $team->getSerie().'V');
                                    break;
                                case 'N':
                                    $team->setPoints($team->getPoints() + 1);
                                    $team->setSerie($team->getSerie().'N');
                                    break;
                                case '2':
                                    $team->setSerie($team->getSerie().'D');
                                    break;
                            }
                        } else {
                            switch ($resultat) {
                                case '1':
                                    $team->setSerie($team->getSerie().'D');
                                    break;
                                case 'N':
                                    $team->setPoints($team->getPoints() + 1);
                                    $team->setSerie($team->getSerie().'N');
                                    break;
                                case '2':
                                    $team->setPoints($team->getPoints() + 3);
                                    $team->setSerie($team->getSerie().'V');
                                    break;
                            }
                        }
                    }
                    $em->persist($team);
                    $cptPersist++;

                    if ($cptPersist == 1000) {
                        $em->flush();
                        $cptPersist = 0;
                    }
                }

                if ($cptPersist > 0) {
                    $em->flush();
                }
            }
            $outcomes = $outcomeRepository->findBy(array('offer' => $offer));
            if(sizeof($outcomes) == 2)
            {
                $outcome = $outcomes[1];
            }
            else
            {
                $outcome = $outcomes[2];
            }

            $resultQuery = $betRepository->findBy(array('outcome' => $outcome, 'strategy' => $strategies[0]));
            if (empty($resultQuery)) {


                $resultQueryHomeTeam = $teamRepository->findBy(array('label' => $labelSplit[0], 'competitionId' => $competitionId));
                $resultQueryAwayTeam = $teamRepository->findBy(array('label' => $labelSplit[1], 'competitionId' => $competitionId));
                if (!empty($resultQueryHomeTeam) && !empty($resultQueryAwayTeam)) {
                    $homeTeam = $resultQueryHomeTeam[0];
                    $awayTeam = $resultQueryAwayTeam[0];
                    $homeSerie = $homeTeam->getSerie();
                    $awaySerie = $awayTeam->getSerie();
                    if (strlen($awaySerie) > 0 && strlen($homeSerie) > 1) {
                        if ($homeSerie[0] == 'D' && $awaySerie[0]== 'V' && $homeSerie[1] == 'D' && ($homeTeam->getPoints() < $awayTeam->getPoints())) {
                            $bet = new Bet();
                            $bet->setStrategy($strategies[0]);
                            $bet->setOutcome($outcome);
                            $em->persist($bet);
                            $em->flush();


                        }
                    }
                }
            }
        }

        $strategies[0]->setWaiting(true);
        $em->persist($strategies[0]);
        $em->flush();

        $strategies = $strategyRepository->findAll();


        return $this->render('FDBetBundle:Bet:index.html.twig', array("strategies" => $strategies));

    }






    public function strategy2LastVictoryHigherRankAway2LastDefeatHomeAction()
    {
        ini_set('memory_limit', '2048M');
        ini_set('max_execution_time', 6000);


        $em = $this->getDoctrine()->getManager('default');
        $outcomeRepository = $em->getRepository('FDOfferBundle:Outcome');
        $offerRepository = $em->getRepository('FDOfferBundle:Offer');
        $strategyRepository = $em->getRepository('FDBetBundle:Strategy');
        $betRepository = $em->getRepository('FDBetBundle:Bet');
        $teamRepository = $em->getRepository('FDTeamBundle:Team');
        $marketResultRepository = $em->getRepository('FDResultBundle:MarketResult');


        $strategies = $strategyRepository->findBy(array('label' => '2LastVictoryHigherRankAway2LastDefeatHome'));

        $bets = $betRepository->findBy(array('strategy' => $strategies[0]));
        $maxOutcome = $bets[sizeof($bets)-1]->getOutcome();

        $offerIds = $outcomeRepository->findAllOfferDistinctHtId($maxOutcome->getId());
        foreach ($offerIds as $offerId) {
            $offer = $offerRepository->find($offerId[1]);
            $date = $offer->getDate();
            $labelSplit = explode('-', $offer->getLabel());
            $competitionId = $offer->getCompetitionId();
            $homeTeamLabel = $labelSplit[0];
            $awayTeamLabel = $labelSplit[1];

            if(substr($homeTeamLabel, -1)== ' ')
            {
                $homeTeamLabel = substr($homeTeamLabel, 0, -1);
            }
            if(substr($awayTeamLabel, -1)== ' ')
            {
                $awayTeamLabel = substr($awayTeamLabel, 0, -1);
            }

            $homeTeam = $teamRepository->findBy(array('label' => $homeTeamLabel, 'competitionId' => $competitionId));
            $awayTeam = $teamRepository->findBy(array('label' => $awayTeamLabel, 'competitionId' => $competitionId));
            if(!empty($homeTeam) && !empty($awayTeam)) {
                $teams = [$homeTeam, $awayTeam];

                $nbHomeMarketResult = $marketResultRepository->findByLabelAndCompetitionIdAndDate($homeTeam[0]->getLabel(), $homeTeam[0]->getCompetitionId(), $date);
                $nbAwayMarketResult = $marketResultRepository->findByLabelAndCompetitionIdAndDate($awayTeam[0]->getLabel(), $awayTeam[0]->getCompetitionId(), $date);
                if(count($nbHomeMarketResult) > count($nbAwayMarketResult))
                {
                    $nbMarketResult = count($nbAwayMarketResult);
                }
                else
                {
                    $nbMarketResult = count($nbHomeMarketResult);
                }

                $cptPersist = 0;

                foreach ($teams as $teamItem) {
                    $team = $teamItem[0];
                    $team->setPoints(0);
                    $team->setSerie('');
                    $teamLabel = $team->getLabel();
                    $marketResultsNoCut = $marketResultRepository->findByLabelAndCompetitionIdAndDate($teamLabel, $team->getCompetitionId(), $date);
                    $marketResults = array_slice($marketResultsNoCut, 0, $nbMarketResult);
                    foreach ($marketResults as $marketResult) {
                        $result = $marketResult->getResult();
                        $resultLabel = $result->getLabel();
                        $resultLabelSplit = explode('-', $resultLabel);
                        $resultat = $marketResult->getResultat();
                        if(substr($resultLabelSplit[0], -1)== ' ')
                        {
                            $resultLabelSplit[0] = substr($resultLabelSplit[0], 0, -1);
                        }
                        if ($resultLabelSplit[0] == $teamLabel) {
                            switch ($resultat) {
                                case '1':
                                    $team->setPoints($team->getPoints() + 3);
                                    $team->setSerie( $team->getSerie().'V');
                                    break;
                                case 'N':
                                    $team->setPoints($team->getPoints() + 1);
                                    $team->setSerie($team->getSerie().'N');
                                    break;
                                case '2':
                                    $team->setSerie($team->getSerie().'D');
                                    break;
                            }
                        } else {
                            switch ($resultat) {
                                case '1':
                                    $team->setSerie($team->getSerie().'D');
                                    break;
                                case 'N':
                                    $team->setPoints($team->getPoints() + 1);
                                    $team->setSerie($team->getSerie().'N');
                                    break;
                                case '2':
                                    $team->setPoints($team->getPoints() + 3);
                                    $team->setSerie($team->getSerie().'V');
                                    break;
                            }
                        }
                    }
                    $em->persist($team);
                    $cptPersist++;

                    if ($cptPersist == 1000) {
                        $em->flush();
                        $cptPersist = 0;
                    }
                }

                if ($cptPersist > 0) {
                    $em->flush();
                }
            }
            $outcomes = $outcomeRepository->findBy(array('offer' => $offer));
            if(sizeof($outcomes) == 2)
            {
                $outcome = $outcomes[1];
            }
            else
            {
                $outcome = $outcomes[2];
            }

            $resultQuery = $betRepository->findBy(array('outcome' => $outcome, 'strategy' => $strategies[0]));
            if (empty($resultQuery)) {


                $resultQueryHomeTeam = $teamRepository->findBy(array('label' => $labelSplit[0], 'competitionId' => $competitionId));
                $resultQueryAwayTeam = $teamRepository->findBy(array('label' => $labelSplit[1], 'competitionId' => $competitionId));
                if (!empty($resultQueryHomeTeam) && !empty($resultQueryAwayTeam)) {
                    $homeTeam = $resultQueryHomeTeam[0];
                    $awayTeam = $resultQueryAwayTeam[0];
                    $homeSerie = $homeTeam->getSerie();
                    $awaySerie = $awayTeam->getSerie();
                    if (strlen($awaySerie) > 1 && strlen($homeSerie) > 1) {
                        if ($awaySerie[1] == 'V' && $homeSerie[0] == 'D' && $awaySerie[0]== 'V' && $homeSerie[1] == 'D' && ($homeTeam->getPoints() < $awayTeam->getPoints())) {
                            $bet = new Bet();
                            $bet->setStrategy($strategies[0]);
                            $bet->setOutcome($outcome);
                            $em->persist($bet);
                            $em->flush();


                        }
                    }
                }
            }
        }

        $strategies[0]->setWaiting(true);
        $em->persist($strategies[0]);
        $em->flush();

        $strategies = $strategyRepository->findAll();


        return $this->render('FDBetBundle:Bet:index.html.twig', array("strategies" => $strategies));

    }





    public function strategy3LastVictoryHigherRankAwayLastDefeatHomeAction()
    {
        ini_set('memory_limit', '2048M');
        ini_set('max_execution_time', 6000);


        $em = $this->getDoctrine()->getManager('default');
        $outcomeRepository = $em->getRepository('FDOfferBundle:Outcome');
        $offerRepository = $em->getRepository('FDOfferBundle:Offer');
        $strategyRepository = $em->getRepository('FDBetBundle:Strategy');
        $betRepository = $em->getRepository('FDBetBundle:Bet');
        $teamRepository = $em->getRepository('FDTeamBundle:Team');
        $marketResultRepository = $em->getRepository('FDResultBundle:MarketResult');


        $strategies = $strategyRepository->findBy(array('label' => '3LastVictoryHigherRankAwayLastDefeatHome'));

        $bets = $betRepository->findBy(array('strategy' => $strategies[0]));
        $maxOutcome = $bets[sizeof($bets)-1]->getOutcome();

        $offerIds = $outcomeRepository->findAllOfferDistinctHtId($maxOutcome->getId());
        foreach ($offerIds as $offerId) {
            $offer = $offerRepository->find($offerId[1]);
            $date = $offer->getDate();
            $labelSplit = explode('-', $offer->getLabel());
            $competitionId = $offer->getCompetitionId();
            $homeTeamLabel = $labelSplit[0];
            $awayTeamLabel = $labelSplit[1];

            if(substr($homeTeamLabel, -1)== ' ')
            {
                $homeTeamLabel = substr($homeTeamLabel, 0, -1);
            }
            if(substr($awayTeamLabel, -1)== ' ')
            {
                $awayTeamLabel = substr($awayTeamLabel, 0, -1);
            }

            $homeTeam = $teamRepository->findBy(array('label' => $homeTeamLabel, 'competitionId' => $competitionId));
            $awayTeam = $teamRepository->findBy(array('label' => $awayTeamLabel, 'competitionId' => $competitionId));
            if(!empty($homeTeam) && !empty($awayTeam)) {
                $teams = [$homeTeam, $awayTeam];

                $nbHomeMarketResult = $marketResultRepository->findByLabelAndCompetitionIdAndDate($homeTeam[0]->getLabel(), $homeTeam[0]->getCompetitionId(), $date);
                $nbAwayMarketResult = $marketResultRepository->findByLabelAndCompetitionIdAndDate($awayTeam[0]->getLabel(), $awayTeam[0]->getCompetitionId(), $date);
                if(count($nbHomeMarketResult) > count($nbAwayMarketResult))
                {
                    $nbMarketResult = count($nbAwayMarketResult);
                }
                else
                {
                    $nbMarketResult = count($nbHomeMarketResult);
                }

                $cptPersist = 0;

                foreach ($teams as $teamItem) {
                    $team = $teamItem[0];
                    $team->setPoints(0);
                    $team->setSerie('');
                    $teamLabel = $team->getLabel();
                    $marketResultsNoCut = $marketResultRepository->findByLabelAndCompetitionIdAndDate($teamLabel, $team->getCompetitionId(), $date);
                    $marketResults = array_slice($marketResultsNoCut, 0, $nbMarketResult);
                    foreach ($marketResults as $marketResult) {
                        $result = $marketResult->getResult();
                        $resultLabel = $result->getLabel();
                        $resultLabelSplit = explode('-', $resultLabel);
                        $resultat = $marketResult->getResultat();
                        if(substr($resultLabelSplit[0], -1)== ' ')
                        {
                            $resultLabelSplit[0] = substr($resultLabelSplit[0], 0, -1);
                        }
                        if ($resultLabelSplit[0] == $teamLabel) {
                            switch ($resultat) {
                                case '1':
                                    $team->setPoints($team->getPoints() + 3);
                                    $team->setSerie( $team->getSerie().'V');
                                    break;
                                case 'N':
                                    $team->setPoints($team->getPoints() + 1);
                                    $team->setSerie($team->getSerie().'N');
                                    break;
                                case '2':
                                    $team->setSerie($team->getSerie().'D');
                                    break;
                            }
                        } else {
                            switch ($resultat) {
                                case '1':
                                    $team->setSerie($team->getSerie().'D');
                                    break;
                                case 'N':
                                    $team->setPoints($team->getPoints() + 1);
                                    $team->setSerie($team->getSerie().'N');
                                    break;
                                case '2':
                                    $team->setPoints($team->getPoints() + 3);
                                    $team->setSerie($team->getSerie().'V');
                                    break;
                            }
                        }
                    }
                    $em->persist($team);
                    $cptPersist++;

                    if ($cptPersist == 1000) {
                        $em->flush();
                        $cptPersist = 0;
                    }
                }

                if ($cptPersist > 0) {
                    $em->flush();
                }
            }
            $outcomes = $outcomeRepository->findBy(array('offer' => $offer));
            if(sizeof($outcomes) == 2)
            {
                $outcome = $outcomes[1];
            }
            else
            {
                $outcome = $outcomes[2];
            }

            $resultQuery = $betRepository->findBy(array('outcome' => $outcome, 'strategy' => $strategies[0]));
            if (empty($resultQuery)) {


                $resultQueryHomeTeam = $teamRepository->findBy(array('label' => $labelSplit[0], 'competitionId' => $competitionId));
                $resultQueryAwayTeam = $teamRepository->findBy(array('label' => $labelSplit[1], 'competitionId' => $competitionId));
                if (!empty($resultQueryHomeTeam) && !empty($resultQueryAwayTeam)) {
                    $homeTeam = $resultQueryHomeTeam[0];
                    $awayTeam = $resultQueryAwayTeam[0];
                    $homeSerie = $homeTeam->getSerie();
                    $awaySerie = $awayTeam->getSerie();
                    if (strlen($awaySerie) > 2 && strlen($homeSerie) > 0) {
                        if ($awaySerie[1] == 'V' && $homeSerie[0] == 'D' && $awaySerie[0]== 'V' && $awaySerie[2] == 'V' && ($homeTeam->getPoints() < $awayTeam->getPoints())) {
                            $bet = new Bet();
                            $bet->setStrategy($strategies[0]);
                            $bet->setOutcome($outcome);
                            $em->persist($bet);
                            $em->flush();


                        }
                    }
                }
            }
        }

        $strategies[0]->setWaiting(true);
        $em->persist($strategies[0]);
        $em->flush();

        $strategies = $strategyRepository->findAll();


        return $this->render('FDBetBundle:Bet:index.html.twig', array("strategies" => $strategies));

    }





    public function strategy3LastVictoryHigherRankAway2LastDefeatHomeAction()
    {
        ini_set('memory_limit', '2048M');
        ini_set('max_execution_time', 6000);


        $em = $this->getDoctrine()->getManager('default');
        $outcomeRepository = $em->getRepository('FDOfferBundle:Outcome');
        $offerRepository = $em->getRepository('FDOfferBundle:Offer');
        $strategyRepository = $em->getRepository('FDBetBundle:Strategy');
        $betRepository = $em->getRepository('FDBetBundle:Bet');
        $teamRepository = $em->getRepository('FDTeamBundle:Team');
        $marketResultRepository = $em->getRepository('FDResultBundle:MarketResult');


        $strategies = $strategyRepository->findBy(array('label' => '3LastVictoryHigherRankAway2LastDefeatHome'));

        $bets = $betRepository->findBy(array('strategy' => $strategies[0]));
        $maxOutcome = $bets[sizeof($bets)-1]->getOutcome();

        $offerIds = $outcomeRepository->findAllOfferDistinctHtId($maxOutcome->getId());
        foreach ($offerIds as $offerId) {
            $offer = $offerRepository->find($offerId[1]);
            $date = $offer->getDate();
            $labelSplit = explode('-', $offer->getLabel());
            $competitionId = $offer->getCompetitionId();
            $homeTeamLabel = $labelSplit[0];
            $awayTeamLabel = $labelSplit[1];

            if(substr($homeTeamLabel, -1)== ' ')
            {
                $homeTeamLabel = substr($homeTeamLabel, 0, -1);
            }
            if(substr($awayTeamLabel, -1)== ' ')
            {
                $awayTeamLabel = substr($awayTeamLabel, 0, -1);
            }

            $homeTeam = $teamRepository->findBy(array('label' => $homeTeamLabel, 'competitionId' => $competitionId));
            $awayTeam = $teamRepository->findBy(array('label' => $awayTeamLabel, 'competitionId' => $competitionId));
            if(!empty($homeTeam) && !empty($awayTeam)) {
                $teams = [$homeTeam, $awayTeam];

                $nbHomeMarketResult = $marketResultRepository->findByLabelAndCompetitionIdAndDate($homeTeam[0]->getLabel(), $homeTeam[0]->getCompetitionId(), $date);
                $nbAwayMarketResult = $marketResultRepository->findByLabelAndCompetitionIdAndDate($awayTeam[0]->getLabel(), $awayTeam[0]->getCompetitionId(), $date);
                if(count($nbHomeMarketResult) > count($nbAwayMarketResult))
                {
                    $nbMarketResult = count($nbAwayMarketResult);
                }
                else
                {
                    $nbMarketResult = count($nbHomeMarketResult);
                }

                $cptPersist = 0;

                foreach ($teams as $teamItem) {
                    $team = $teamItem[0];
                    $team->setPoints(0);
                    $team->setSerie('');
                    $teamLabel = $team->getLabel();
                    $marketResultsNoCut = $marketResultRepository->findByLabelAndCompetitionIdAndDate($teamLabel, $team->getCompetitionId(), $date);
                    $marketResults = array_slice($marketResultsNoCut, 0, $nbMarketResult);
                    foreach ($marketResults as $marketResult) {
                        $result = $marketResult->getResult();
                        $resultLabel = $result->getLabel();
                        $resultLabelSplit = explode('-', $resultLabel);
                        $resultat = $marketResult->getResultat();
                        if(substr($resultLabelSplit[0], -1)== ' ')
                        {
                            $resultLabelSplit[0] = substr($resultLabelSplit[0], 0, -1);
                        }
                        if ($resultLabelSplit[0] == $teamLabel) {
                            switch ($resultat) {
                                case '1':
                                    $team->setPoints($team->getPoints() + 3);
                                    $team->setSerie( $team->getSerie().'V');
                                    break;
                                case 'N':
                                    $team->setPoints($team->getPoints() + 1);
                                    $team->setSerie($team->getSerie().'N');
                                    break;
                                case '2':
                                    $team->setSerie($team->getSerie().'D');
                                    break;
                            }
                        } else {
                            switch ($resultat) {
                                case '1':
                                    $team->setSerie($team->getSerie().'D');
                                    break;
                                case 'N':
                                    $team->setPoints($team->getPoints() + 1);
                                    $team->setSerie($team->getSerie().'N');
                                    break;
                                case '2':
                                    $team->setPoints($team->getPoints() + 3);
                                    $team->setSerie($team->getSerie().'V');
                                    break;
                            }
                        }
                    }
                    $em->persist($team);
                    $cptPersist++;

                    if ($cptPersist == 1000) {
                        $em->flush();
                        $cptPersist = 0;
                    }
                }

                if ($cptPersist > 0) {
                    $em->flush();
                }
            }
            $outcomes = $outcomeRepository->findBy(array('offer' => $offer));
            if(sizeof($outcomes) == 2)
            {
                $outcome = $outcomes[1];
            }
            else
            {
                $outcome = $outcomes[2];
            }

            $resultQuery = $betRepository->findBy(array('outcome' => $outcome, 'strategy' => $strategies[0]));
            if (empty($resultQuery)) {


                $resultQueryHomeTeam = $teamRepository->findBy(array('label' => $labelSplit[0], 'competitionId' => $competitionId));
                $resultQueryAwayTeam = $teamRepository->findBy(array('label' => $labelSplit[1], 'competitionId' => $competitionId));
                if (!empty($resultQueryHomeTeam) && !empty($resultQueryAwayTeam)) {
                    $homeTeam = $resultQueryHomeTeam[0];
                    $awayTeam = $resultQueryAwayTeam[0];
                    $homeSerie = $homeTeam->getSerie();
                    $awaySerie = $awayTeam->getSerie();
                    if (strlen($awaySerie) > 2 && strlen($homeSerie) > 1) {
                        if ($awaySerie[1] == 'V' && $homeSerie[1] == 'D' && $homeSerie[0] == 'D' && $awaySerie[0]== 'V' && $awaySerie[2] == 'V' && ($homeTeam->getPoints() < $awayTeam->getPoints())) {
                            $bet = new Bet();
                            $bet->setStrategy($strategies[0]);
                            $bet->setOutcome($outcome);
                            $em->persist($bet);
                            $em->flush();


                        }
                    }
                }
            }
        }

        $strategies[0]->setWaiting(true);
        $em->persist($strategies[0]);
        $em->flush();

        $strategies = $strategyRepository->findAll();


        return $this->render('FDBetBundle:Bet:index.html.twig', array("strategies" => $strategies));

    }








    public function strategyLastVictoryHigherRankAway3LastDefeatHomeAction()
    {
        ini_set('memory_limit', '2048M');
        ini_set('max_execution_time', 6000);


        $em = $this->getDoctrine()->getManager('default');
        $outcomeRepository = $em->getRepository('FDOfferBundle:Outcome');
        $offerRepository = $em->getRepository('FDOfferBundle:Offer');
        $strategyRepository = $em->getRepository('FDBetBundle:Strategy');
        $betRepository = $em->getRepository('FDBetBundle:Bet');
        $teamRepository = $em->getRepository('FDTeamBundle:Team');
        $marketResultRepository = $em->getRepository('FDResultBundle:MarketResult');


        $strategies = $strategyRepository->findBy(array('label' => 'LastVictoryHigherRankAway3LastDefeatHome'));

        $bets = $betRepository->findBy(array('strategy' => $strategies[0]));
        $maxOutcome = $bets[sizeof($bets)-1]->getOutcome();

        $offerIds = $outcomeRepository->findAllOfferDistinctHtId($maxOutcome->getId());
        foreach ($offerIds as $offerId) {
            $offer = $offerRepository->find($offerId[1]);
            $date = $offer->getDate();
            $labelSplit = explode('-', $offer->getLabel());
            $competitionId = $offer->getCompetitionId();
            $homeTeamLabel = $labelSplit[0];
            $awayTeamLabel = $labelSplit[1];

            if(substr($homeTeamLabel, -1)== ' ')
            {
                $homeTeamLabel = substr($homeTeamLabel, 0, -1);
            }
            if(substr($awayTeamLabel, -1)== ' ')
            {
                $awayTeamLabel = substr($awayTeamLabel, 0, -1);
            }

            $homeTeam = $teamRepository->findBy(array('label' => $homeTeamLabel, 'competitionId' => $competitionId));
            $awayTeam = $teamRepository->findBy(array('label' => $awayTeamLabel, 'competitionId' => $competitionId));
            if(!empty($homeTeam) && !empty($awayTeam)) {
                $teams = [$homeTeam, $awayTeam];

                $nbHomeMarketResult = $marketResultRepository->findByLabelAndCompetitionIdAndDate($homeTeam[0]->getLabel(), $homeTeam[0]->getCompetitionId(), $date);
                $nbAwayMarketResult = $marketResultRepository->findByLabelAndCompetitionIdAndDate($awayTeam[0]->getLabel(), $awayTeam[0]->getCompetitionId(), $date);
                if(count($nbHomeMarketResult) > count($nbAwayMarketResult))
                {
                    $nbMarketResult = count($nbAwayMarketResult);
                }
                else
                {
                    $nbMarketResult = count($nbHomeMarketResult);
                }

                $cptPersist = 0;

                foreach ($teams as $teamItem) {
                    $team = $teamItem[0];
                    $team->setPoints(0);
                    $team->setSerie('');
                    $teamLabel = $team->getLabel();
                    $marketResultsNoCut = $marketResultRepository->findByLabelAndCompetitionIdAndDate($teamLabel, $team->getCompetitionId(), $date);
                    $marketResults = array_slice($marketResultsNoCut, 0, $nbMarketResult);
                    foreach ($marketResults as $marketResult) {
                        $result = $marketResult->getResult();
                        $resultLabel = $result->getLabel();
                        $resultLabelSplit = explode('-', $resultLabel);
                        $resultat = $marketResult->getResultat();
                        if(substr($resultLabelSplit[0], -1)== ' ')
                        {
                            $resultLabelSplit[0] = substr($resultLabelSplit[0], 0, -1);
                        }
                        if ($resultLabelSplit[0] == $teamLabel) {
                            switch ($resultat) {
                                case '1':
                                    $team->setPoints($team->getPoints() + 3);
                                    $team->setSerie( $team->getSerie().'V');
                                    break;
                                case 'N':
                                    $team->setPoints($team->getPoints() + 1);
                                    $team->setSerie($team->getSerie().'N');
                                    break;
                                case '2':
                                    $team->setSerie($team->getSerie().'D');
                                    break;
                            }
                        } else {
                            switch ($resultat) {
                                case '1':
                                    $team->setSerie($team->getSerie().'D');
                                    break;
                                case 'N':
                                    $team->setPoints($team->getPoints() + 1);
                                    $team->setSerie($team->getSerie().'N');
                                    break;
                                case '2':
                                    $team->setPoints($team->getPoints() + 3);
                                    $team->setSerie($team->getSerie().'V');
                                    break;
                            }
                        }
                    }
                    $em->persist($team);
                    $cptPersist++;

                    if ($cptPersist == 1000) {
                        $em->flush();
                        $cptPersist = 0;
                    }
                }

                if ($cptPersist > 0) {
                    $em->flush();
                }
            }
            $outcomes = $outcomeRepository->findBy(array('offer' => $offer));
            if(sizeof($outcomes) == 2)
            {
                $outcome = $outcomes[1];
            }
            else
            {
                $outcome = $outcomes[2];
            }

            $resultQuery = $betRepository->findBy(array('outcome' => $outcome, 'strategy' => $strategies[0]));
            if (empty($resultQuery)) {


                $resultQueryHomeTeam = $teamRepository->findBy(array('label' => $labelSplit[0], 'competitionId' => $competitionId));
                $resultQueryAwayTeam = $teamRepository->findBy(array('label' => $labelSplit[1], 'competitionId' => $competitionId));
                if (!empty($resultQueryHomeTeam) && !empty($resultQueryAwayTeam)) {
                    $homeTeam = $resultQueryHomeTeam[0];
                    $awayTeam = $resultQueryAwayTeam[0];
                    $homeSerie = $homeTeam->getSerie();
                    $awaySerie = $awayTeam->getSerie();
                    if (strlen($awaySerie) > 0 && strlen($homeSerie) > 2) {
                        if ($homeSerie[0] == 'D' && $awaySerie[0]== 'V' && $homeSerie[1] == 'D' && $homeSerie[2] == 'D' && ($homeTeam->getPoints() < $awayTeam->getPoints())) {
                            $bet = new Bet();
                            $bet->setStrategy($strategies[0]);
                            $bet->setOutcome($outcome);
                            $em->persist($bet);
                            $em->flush();


                        }
                    }
                }
            }
        }

        $strategies[0]->setWaiting(true);
        $em->persist($strategies[0]);
        $em->flush();

        $strategies = $strategyRepository->findAll();


        return $this->render('FDBetBundle:Bet:index.html.twig', array("strategies" => $strategies));

    }




    public function strategy2LastVictoryHigherRankAway3LastDefeatHomeAction()
    {
        ini_set('memory_limit', '2048M');
        ini_set('max_execution_time', 6000);


        $em = $this->getDoctrine()->getManager('default');
        $outcomeRepository = $em->getRepository('FDOfferBundle:Outcome');
        $offerRepository = $em->getRepository('FDOfferBundle:Offer');
        $strategyRepository = $em->getRepository('FDBetBundle:Strategy');
        $betRepository = $em->getRepository('FDBetBundle:Bet');
        $teamRepository = $em->getRepository('FDTeamBundle:Team');
        $marketResultRepository = $em->getRepository('FDResultBundle:MarketResult');


        $strategies = $strategyRepository->findBy(array('label' => '2LastVictoryHigherRankAway3LastDefeatHome'));

        $bets = $betRepository->findBy(array('strategy' => $strategies[0]));
        $maxOutcome = $bets[sizeof($bets)-1]->getOutcome();

        $offerIds = $outcomeRepository->findAllOfferDistinctHtId($maxOutcome->getId());
        foreach ($offerIds as $offerId) {
            $offer = $offerRepository->find($offerId[1]);
            $date = $offer->getDate();
            $labelSplit = explode('-', $offer->getLabel());
            $competitionId = $offer->getCompetitionId();
            $homeTeamLabel = $labelSplit[0];
            $awayTeamLabel = $labelSplit[1];

            if(substr($homeTeamLabel, -1)== ' ')
            {
                $homeTeamLabel = substr($homeTeamLabel, 0, -1);
            }
            if(substr($awayTeamLabel, -1)== ' ')
            {
                $awayTeamLabel = substr($awayTeamLabel, 0, -1);
            }

            $homeTeam = $teamRepository->findBy(array('label' => $homeTeamLabel, 'competitionId' => $competitionId));
            $awayTeam = $teamRepository->findBy(array('label' => $awayTeamLabel, 'competitionId' => $competitionId));
            if(!empty($homeTeam) && !empty($awayTeam)) {
                $teams = [$homeTeam, $awayTeam];

                $nbHomeMarketResult = $marketResultRepository->findByLabelAndCompetitionIdAndDate($homeTeam[0]->getLabel(), $homeTeam[0]->getCompetitionId(), $date);
                $nbAwayMarketResult = $marketResultRepository->findByLabelAndCompetitionIdAndDate($awayTeam[0]->getLabel(), $awayTeam[0]->getCompetitionId(), $date);
                if(count($nbHomeMarketResult) > count($nbAwayMarketResult))
                {
                    $nbMarketResult = count($nbAwayMarketResult);
                }
                else
                {
                    $nbMarketResult = count($nbHomeMarketResult);
                }

                $cptPersist = 0;

                foreach ($teams as $teamItem) {
                    $team = $teamItem[0];
                    $team->setPoints(0);
                    $team->setSerie('');
                    $teamLabel = $team->getLabel();
                    $marketResultsNoCut = $marketResultRepository->findByLabelAndCompetitionIdAndDate($teamLabel, $team->getCompetitionId(), $date);
                    $marketResults = array_slice($marketResultsNoCut, 0, $nbMarketResult);
                    foreach ($marketResults as $marketResult) {
                        $result = $marketResult->getResult();
                        $resultLabel = $result->getLabel();
                        $resultLabelSplit = explode('-', $resultLabel);
                        $resultat = $marketResult->getResultat();
                        if(substr($resultLabelSplit[0], -1)== ' ')
                        {
                            $resultLabelSplit[0] = substr($resultLabelSplit[0], 0, -1);
                        }
                        if ($resultLabelSplit[0] == $teamLabel) {
                            switch ($resultat) {
                                case '1':
                                    $team->setPoints($team->getPoints() + 3);
                                    $team->setSerie( $team->getSerie().'V');
                                    break;
                                case 'N':
                                    $team->setPoints($team->getPoints() + 1);
                                    $team->setSerie($team->getSerie().'N');
                                    break;
                                case '2':
                                    $team->setSerie($team->getSerie().'D');
                                    break;
                            }
                        } else {
                            switch ($resultat) {
                                case '1':
                                    $team->setSerie($team->getSerie().'D');
                                    break;
                                case 'N':
                                    $team->setPoints($team->getPoints() + 1);
                                    $team->setSerie($team->getSerie().'N');
                                    break;
                                case '2':
                                    $team->setPoints($team->getPoints() + 3);
                                    $team->setSerie($team->getSerie().'V');
                                    break;
                            }
                        }
                    }
                    $em->persist($team);
                    $cptPersist++;

                    if ($cptPersist == 1000) {
                        $em->flush();
                        $cptPersist = 0;
                    }
                }

                if ($cptPersist > 0) {
                    $em->flush();
                }
            }
            $outcomes = $outcomeRepository->findBy(array('offer' => $offer));
            if(sizeof($outcomes) == 2)
            {
                $outcome = $outcomes[1];
            }
            else
            {
                $outcome = $outcomes[2];
            }

            $resultQuery = $betRepository->findBy(array('outcome' => $outcome, 'strategy' => $strategies[0]));
            if (empty($resultQuery)) {


                $resultQueryHomeTeam = $teamRepository->findBy(array('label' => $labelSplit[0], 'competitionId' => $competitionId));
                $resultQueryAwayTeam = $teamRepository->findBy(array('label' => $labelSplit[1], 'competitionId' => $competitionId));
                if (!empty($resultQueryHomeTeam) && !empty($resultQueryAwayTeam)) {
                    $homeTeam = $resultQueryHomeTeam[0];
                    $awayTeam = $resultQueryAwayTeam[0];
                    $homeSerie = $homeTeam->getSerie();
                    $awaySerie = $awayTeam->getSerie();
                    if (strlen($awaySerie) > 1 && strlen($homeSerie) > 2) {
                        if ($awaySerie[1] == 'V' && $homeSerie[0] == 'D' && $awaySerie[0]== 'V' && $homeSerie[1] == 'D' && $homeSerie[2] == 'D' && ($homeTeam->getPoints() < $awayTeam->getPoints())) {
                            $bet = new Bet();
                            $bet->setStrategy($strategies[0]);
                            $bet->setOutcome($outcome);
                            $em->persist($bet);
                            $em->flush();


                        }
                    }
                }
            }
        }


        $strategies[0]->setWaiting(true);
        $em->persist($strategies[0]);
        $em->flush();

        $strategies = $strategyRepository->findAll();


        return $this->render('FDBetBundle:Bet:index.html.twig', array("strategies" => $strategies));

    }



    public function strategy3LastVictoryHigherRankAway3LastDefeatHomeAction()
    {
        ini_set('memory_limit', '2048M');
        ini_set('max_execution_time', 6000);


        $em = $this->getDoctrine()->getManager('default');
        $outcomeRepository = $em->getRepository('FDOfferBundle:Outcome');
        $offerRepository = $em->getRepository('FDOfferBundle:Offer');
        $strategyRepository = $em->getRepository('FDBetBundle:Strategy');
        $betRepository = $em->getRepository('FDBetBundle:Bet');
        $teamRepository = $em->getRepository('FDTeamBundle:Team');
        $marketResultRepository = $em->getRepository('FDResultBundle:MarketResult');


        $strategies = $strategyRepository->findBy(array('label' => '3LastVictoryHigherRankAway3LastDefeatHome'));

        $bets = $betRepository->findBy(array('strategy' => $strategies[0]));
        $maxOutcome = $bets[sizeof($bets)-1]->getOutcome();

        $offerIds = $outcomeRepository->findAllOfferDistinctHtId($maxOutcome->getId());
        foreach ($offerIds as $offerId) {
            $offer = $offerRepository->find($offerId[1]);
            $date = $offer->getDate();
            $labelSplit = explode('-', $offer->getLabel());
            $competitionId = $offer->getCompetitionId();
            $homeTeamLabel = $labelSplit[0];
            $awayTeamLabel = $labelSplit[1];

            if(substr($homeTeamLabel, -1)== ' ')
            {
                $homeTeamLabel = substr($homeTeamLabel, 0, -1);
            }
            if(substr($awayTeamLabel, -1)== ' ')
            {
                $awayTeamLabel = substr($awayTeamLabel, 0, -1);
            }

            $homeTeam = $teamRepository->findBy(array('label' => $homeTeamLabel, 'competitionId' => $competitionId));
            $awayTeam = $teamRepository->findBy(array('label' => $awayTeamLabel, 'competitionId' => $competitionId));
            if(!empty($homeTeam) && !empty($awayTeam)) {
                $teams = [$homeTeam, $awayTeam];

                $nbHomeMarketResult = $marketResultRepository->findByLabelAndCompetitionIdAndDate($homeTeam[0]->getLabel(), $homeTeam[0]->getCompetitionId(), $date);
                $nbAwayMarketResult = $marketResultRepository->findByLabelAndCompetitionIdAndDate($awayTeam[0]->getLabel(), $awayTeam[0]->getCompetitionId(), $date);
                if(count($nbHomeMarketResult) > count($nbAwayMarketResult))
                {
                    $nbMarketResult = count($nbAwayMarketResult);
                }
                else
                {
                    $nbMarketResult = count($nbHomeMarketResult);
                }

                $cptPersist = 0;

                foreach ($teams as $teamItem) {
                    $team = $teamItem[0];
                    $team->setPoints(0);
                    $team->setSerie('');
                    $teamLabel = $team->getLabel();
                    $marketResultsNoCut = $marketResultRepository->findByLabelAndCompetitionIdAndDate($teamLabel, $team->getCompetitionId(), $date);
                    $marketResults = array_slice($marketResultsNoCut, 0, $nbMarketResult);
                    foreach ($marketResults as $marketResult) {
                        $result = $marketResult->getResult();
                        $resultLabel = $result->getLabel();
                        $resultLabelSplit = explode('-', $resultLabel);
                        $resultat = $marketResult->getResultat();
                        if(substr($resultLabelSplit[0], -1)== ' ')
                        {
                            $resultLabelSplit[0] = substr($resultLabelSplit[0], 0, -1);
                        }
                        if ($resultLabelSplit[0] == $teamLabel) {
                            switch ($resultat) {
                                case '1':
                                    $team->setPoints($team->getPoints() + 3);
                                    $team->setSerie( $team->getSerie().'V');
                                    break;
                                case 'N':
                                    $team->setPoints($team->getPoints() + 1);
                                    $team->setSerie($team->getSerie().'N');
                                    break;
                                case '2':
                                    $team->setSerie($team->getSerie().'D');
                                    break;
                            }
                        } else {
                            switch ($resultat) {
                                case '1':
                                    $team->setSerie($team->getSerie().'D');
                                    break;
                                case 'N':
                                    $team->setPoints($team->getPoints() + 1);
                                    $team->setSerie($team->getSerie().'N');
                                    break;
                                case '2':
                                    $team->setPoints($team->getPoints() + 3);
                                    $team->setSerie($team->getSerie().'V');
                                    break;
                            }
                        }
                    }
                    $em->persist($team);
                    $cptPersist++;

                    if ($cptPersist == 1000) {
                        $em->flush();
                        $cptPersist = 0;
                    }
                }

                if ($cptPersist > 0) {
                    $em->flush();
                }
            }
            $outcomes = $outcomeRepository->findBy(array('offer' => $offer));
            if(sizeof($outcomes) == 2)
            {
                $outcome = $outcomes[1];
            }
            else
            {
                $outcome = $outcomes[2];
            }
            $strategies = $strategyRepository->findBy(array('label' => '3LastVictoryHigherRankAway3LastDefeatHome'));

            $resultQuery = $betRepository->findBy(array('outcome' => $outcome, 'strategy' => $strategies[0]));
            if (empty($resultQuery)) {


                $resultQueryHomeTeam = $teamRepository->findBy(array('label' => $labelSplit[0], 'competitionId' => $competitionId));
                $resultQueryAwayTeam = $teamRepository->findBy(array('label' => $labelSplit[1], 'competitionId' => $competitionId));
                if (!empty($resultQueryHomeTeam) && !empty($resultQueryAwayTeam)) {
                    $homeTeam = $resultQueryHomeTeam[0];
                    $awayTeam = $resultQueryAwayTeam[0];
                    $homeSerie = $homeTeam->getSerie();
                    $awaySerie = $awayTeam->getSerie();
                    if (strlen($awaySerie) > 2 && strlen($homeSerie) > 2) {
                        if ($awaySerie[2] == 'V' && $awaySerie[1] == 'V' && $homeSerie[0] == 'D' && $awaySerie[0]== 'V' && $homeSerie[1] == 'D' && $homeSerie[2] == 'D' && ($homeTeam->getPoints() < $awayTeam->getPoints())) {
                            $bet = new Bet();
                            $bet->setStrategy($strategies[0]);
                            $bet->setOutcome($outcome);
                            $em->persist($bet);
                            $em->flush();


                        }
                    }
                }
            }
        }

        $strategies[0]->setWaiting(true);
        $em->persist($strategies[0]);
        $em->flush();

        $strategies = $strategyRepository->findAll();


        return $this->render('FDBetBundle:Bet:index.html.twig', array("strategies" => $strategies));

    }
}