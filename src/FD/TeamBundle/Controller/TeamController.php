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
        ini_set('memory_limit', '512M');
        ini_set('max_execution_time', 18000);

        $dateUp = new DateTime();
        $dateUp->setDate(2016,4,1);

        $dateDown = new DateTime();
        $dateDown->setDate(2016,3,1);

        $dateEnd = new DateTime();
        $dateEnd->setDate(2016,4,22);

        $em = $this->getDoctrine()->getManager('default');
        $marketResultRepository = $em->getRepository('FDResultBundle:MarketResult');
        $teamRepository = $em->getRepository('FDTeamBundle:Team');
        while($dateUp < $dateEnd) {

            $marketResults = $marketResultRepository->findByBetweenDate($dateUp, $dateDown);
            $result = null;
            foreach ($marketResults as $marketResult) {
                $result = $marketResult->getResult();
                $label = $result->getLabel();
                if (substr_count($label, '-') == 1) {
                    $competitionId = $result->getCompetitionId();
                    $labelSplit = explode('-', $label);

                    $homeTeam = null;
                    $awayTeam = null;

                    $resultQuery = $teamRepository->findBy(array('label' => $labelSplit[0], 'competitionId' => $competitionId));
                    if(empty($resultQuery)) {
                        $homeTeam = new Team();
                        $homeTeam->setCompetitionId($competitionId);
                        $homeTeam->setLabel($labelSplit[0]);
                        $homeTeam->setPoints(0);
                        $homeTeam->setRank(0);
                        $homeTeam->setSerie('');
                        $em->persist($homeTeam);

                    }

                    $resultQuery = $teamRepository->findBy(array('label' => $labelSplit[1], 'competitionId' => $competitionId));
                    if(empty($resultQuery)) {
                        $awayTeam = new Team();
                        $awayTeam->setCompetitionId($competitionId);
                        $awayTeam->setLabel($labelSplit[1]);
                        $awayTeam->setPoints(0);
                        $awayTeam->setRank(0);
                        $awayTeam->setSerie('');
                        $em->persist($awayTeam);
                    }

                    $em->flush();
                }
            }
            $dateUp->modify('+1 month');
            $dateDown->modify('+1 month');
        }
        return new Response("Hello World");
    }

    public function resultAction()
    {
        $em = $this->getDoctrine()->getManager('default');
        $teamRepository = $em->getRepository('FDTeamBundle:Team');
        $marketResultRepository = $em->getRepository('FDResultBundle:MarketResult');

        $teams = $teamRepository->findAll();

        foreach($teams as $team)
        {
            $teamLabel = $team->getLabel();
            $marketResults = $marketResultRepository->findByLabelAndCompetitionId($teamLabel, $team->getCompetitionId());
            foreach($marketResults as $marketResult)
            {
                $result = $marketResult->getResult();
                $resultLabel = $result->getLabel();
                $resultLabelSplit = explode('-', $resultLabel);
                if($resultLabelSplit[0] == $teamLabel)
                {
                    $resultat = $marketResult->getResultat();
                    switch($resultat)
                    {
                        case '1':
                            $team->setPoints($team->getPoints() + 3);
                            $team->setSerie('V'.$team->getSerie());
                            break;
                        case 'N':
                            $team->setPoints($team->getPoints() + 1);
                            $team->setSerie('N'.$team->getSerie());
                            break;
                        case '2':
                            $team->setSerie('D'.$team->getSerie());
                            break;
                    }
                }
            }
        }

        return new Response("Hello World");
    }

}