<?php

namespace FD\TeamBundle\Controller;

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
        $em = $this->getDoctrine()->getManager('default');
        $marketResultRepository = $em->getRepository('FDResultBundle:MarketResult');

        $marketResults = $marketResultRepository->findAll();
        $result = null;
        foreach($marketResults as $marketResultItem)
        {
            $result = $marketResultItem->getResult();
            $label = $result->getLabel();
            if(substr_count($label, '-') == 1) {
                $competitionId = $result->getCompetitionId();
                $labelSplit = explode('-', $label);
                $homeTeam = new Team();
                $homeTeam->setCompetitionId($competitionId);
                $homeTeam->setLabel($labelSplit[0]);
                $homeTeam->setPoints(0);
                $homeTeam->setRank(0);
                $homeTeam->setSerie('');
                $awayTeam = new Team();
                $awayTeam->setCompetitionId($competitionId);
                $awayTeam->setLabel($labelSplit[1]);
                $awayTeam->setPoints(0);
                $awayTeam->setRank(0);
                $awayTeam->setSerie('');
                $em->persist($homeTeam);
                $em->persist($awayTeam);
                $em->flush();
            }
        }
        return new Response("Hello World");
    }

}