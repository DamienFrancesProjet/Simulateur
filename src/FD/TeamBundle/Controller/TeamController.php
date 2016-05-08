<?php

namespace FD\TeamBundle\Controller;

use DateTime;
use FD\TeamBundle\Entity\Team;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;


/**
 * Created by PhpStorm.
 * User: Damien
 * Date: 19/04/2016
 * Time: 00:17
 */
class TeamController extends Controller
{
    public function getAction()
    {
        ini_set('memory_limit', '2048M');
        ini_set('max_execution_time', 6000);

        $em = $this->getDoctrine()->getManager('default');
        $marketResultRepository = $em->getRepository('FDResultBundle:MarketResult');
        $teamRepository = $em->getRepository('FDTeamBundle:Team');

        $dateEnd = new DateTime();
        $dateUp = new DateTime();
        $dateDown = new DateTime();
        $dateDown->modify('-1 month');

        while($dateDown < $dateEnd) {

            $marketResults = $marketResultRepository->findByBetweenDate($dateUp, $dateDown);
            $result = null;
            foreach ($marketResults as $marketResult) {
                $result = $marketResult->getResult();
                $label = $result->getLabel();
                $competitionId = $result->getCompetitionId();
                $labelSplit = explode('-', $label);
                $homeTeam = null;
                $awayTeam = null;

                if (substr($labelSplit[0], -1) == ' ') {
                    $labelSplit[0] = substr($labelSplit[0], 0, -1);
                }
                if (substr($labelSplit[1], -1) == ' ') {
                    $labelSplit[1] = substr($labelSplit[1], 0, -1);
                }

                $resultQuery = $teamRepository->findBy(array('label' => $labelSplit[0], 'competitionId' => $competitionId));
                if (empty($resultQuery)) {
                    $homeTeam = new Team();
                    $homeTeam->setCompetitionId($competitionId);
                    $homeTeam->setLabel($labelSplit[0]);
                    $homeTeam->setPoints(0);
                    $homeTeam->setSerie('');
                    $em->persist($homeTeam);
                    $em->flush();
                }
                $resultQuery = $teamRepository->findBy(array('label' => $labelSplit[1], 'competitionId' => $competitionId));
                if (empty($resultQuery)) {
                    $awayTeam = new Team();
                    $awayTeam->setCompetitionId($competitionId);
                    $awayTeam->setLabel($labelSplit[1]);
                    $awayTeam->setPoints(0);
                    $awayTeam->setSerie('');
                    $em->persist($awayTeam);
                    $em->flush();
                }

            }

            $dateDown->modify('+1 month');
            $dateUp->modify('+1 month');
        }

        var_dump("Team Get OK");

        return new Response("Hello World");
    }

    public function resultAction($date, $homeTeamLabel, $awayTeamLabel, $competitionId)
    {
        ini_set('memory_limit', '2048M');

        $em = $this->getDoctrine()->getManager('default');
        $teamRepository = $em->getRepository('FDTeamBundle:Team');
        $marketResultRepository = $em->getRepository('FDResultBundle:MarketResult');

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

        return new Response("Hello World");
    }

    /*public function rankAction()
    {
        ini_set('max_execution_time', 6000);
        ini_set('memory_limit', '2048M');

        $cptPersist = 0;
        $em = $this->getDoctrine()->getManager('default');
        $teamRepository = $em->getRepository('FDTeamBundle:Team');
        $competitionIds = $teamRepository->findAllCompetitionId();
        foreach($competitionIds as $competitionId)
        {
            $ranking = $teamRepository->getRanking($competitionId['competitionId']);
            $i = 1;
            foreach($ranking as $teamRank)
            {
                $team = $teamRepository->find($teamRank->getId());
                $team->setRank($i);
                $i++;
                $em->persist($team);
                $cptPersist++;

                if($cptPersist == 1000)
                {
                    $em->flush();
                    $cptPersist = 0;
                }
            }
        }

        if($cptPersist > 0)
        {
            $em->flush();
        }

        var_dump("Team Rank OK");

        return new Response("Hello World");
    }*/

}