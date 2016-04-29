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

        $cptPersist = 0;
        $em = $this->getDoctrine()->getManager('default');
        $marketResultRepository = $em->getRepository('FDResultBundle:MarketResult');
        $teamRepository = $em->getRepository('FDTeamBundle:Team');
            $marketResults = $marketResultRepository->findAll();
            $result = null;
            foreach ($marketResults as $marketResult) {
                $result = $marketResult->getResult();
                $label = $result->getLabel();
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
                        $cptPersist++;
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
                        $cptPersist++;
                    }
                if($cptPersist == 1000)
                {
                    $em->flush();
                    $cptPersist = 0;
                }

            }
        if($cptPersist > 0)
        {
            $em->flush();
        }

        var_dump("Team Get OK");

        return new Response("Hello World");
    }

    public function resultAction()
    {
        ini_set('max_execution_time', 6000);
        ini_set('memory_limit', '2048M');

        $em = $this->getDoctrine()->getManager('default');
        $teamRepository = $em->getRepository('FDTeamBundle:Team');
        $marketResultRepository = $em->getRepository('FDResultBundle:MarketResult');

        $teams = $teamRepository->findAll();

        $cptPersist = 0;

        foreach($teams as $team)
        {
            $team->setPoints(0);
            $team->setSerie('');
            $teamLabel = $team->getLabel();
            $marketResults = $marketResultRepository->findByLabelAndCompetitionId($teamLabel, $team->getCompetitionId());
            foreach($marketResults as $marketResult)
            {
                $result = $marketResult->getResult();
                $resultLabel = $result->getLabel();
                $resultLabelSplit = explode('-', $resultLabel);
                $resultat = $marketResult->getResultat();
                if($resultLabelSplit[0] == $teamLabel)
                {
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
                else
                {
                    switch($resultat)
                    {
                        case '1':
                            $team->setSerie('D'.$team->getSerie());
                            break;
                        case 'N':
                            $team->setPoints($team->getPoints() + 1);
                            $team->setSerie('N'.$team->getSerie());
                            break;
                        case '2':
                            $team->setPoints($team->getPoints() + 3);
                            $team->setSerie('V'.$team->getSerie());
                            break;
                    }
                }
            }
            $em->persist($team);
            $cptPersist++;

            if($cptPersist == 1000)
            {
                $em->flush();
                $cptPersist = 0;
            }
        }

        if($cptPersist > 0)
        {
            $em->flush();
        }

        var_dump("Team Result OK");

        return new Response("Hello World");
    }

    public function rankAction()
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
    }

}