<?php
/**
 * Created by PhpStorm.
 * User: Damien
 * Date: 18/04/2016
 * Time: 01:25
 */

namespace FD\ResultBundle\Repository;


class MarketResultRepository extends \Doctrine\ORM\EntityRepository
{
    public function findByBetweenDate($dateUp, $dateDown)
    {
        $query = $this->_em->createQuery('SELECT mr FROM FDResultBundle:MarketResult mr JOIN mr.result r WHERE r.date < :dateUp AND r.date >= :dateDown');
        $query->setParameters(array('dateUp' => $dateUp, 'dateDown' => $dateDown));
        return $query->getResult();
    }

    public function findByLtDate($date)
    {
        $query = $this->_em->createQuery('SELECT mr FROM FDResultBundle:MarketResult mr JOIN mr.result r WHERE r.date < :date');
        $query->setParameter('date', $date);
        return $query->getResult();
    }

    public function findByLabelAndCompetitionIdAndDate($label, $competitionId, $date)
    {
        $query = $this->_em->createQuery('SELECT mr FROM FDResultBundle:MarketResult mr JOIN mr.result r WHERE r.label LIKE :label AND r.competitionId = :competitionId AND r.date < :date ORDER BY r.date DESC');
        $query->setParameters(array('label' => '%'.$label.'%', 'competitionId' => $competitionId, 'date' => $date));
        return $query->getResult();
    }

    public function findByEventId($eventId)
    {
        $query = $this->_em->createQuery('SELECT mr FROM FDResultBundle:MarketResult mr JOIN mr.result r WHERE r.eventId = :eventId');
        $query->setParameter('eventId', $eventId);
        return $query->getResult();
    }
}