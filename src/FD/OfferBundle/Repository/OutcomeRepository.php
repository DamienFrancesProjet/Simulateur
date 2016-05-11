<?php
/**
 * Created by PhpStorm.
 * User: Damien
 * Date: 17/04/2016
 * Time: 19:19
 */

namespace FD\OfferBundle\Repository;


class OutcomeRepository extends \Doctrine\ORM\EntityRepository
{
    public function findAllOfferDistinctBetweenDate($dateUp, $dateDown)
    {
        $query = $this->_em->createQuery('SELECT DISTINCT IDENTITY(ou.offer) FROM FDOfferBundle:Outcome ou JOIN ou.offer o WHERE o.date < :dateUp AND o.date >= :dateDown');
        $query->setParameters(array('dateUp' => $dateUp, 'dateDown' => $dateDown));
        return $query->getResult();
    }
    public function findAllOfferDistinctHtId($id)
    {
        $query = $this->_em->createQuery('SELECT DISTINCT IDENTITY(ou.offer) FROM FDOfferBundle:Outcome ou WHERE ou.id > :id');
        $query->setParameter('id', $id);
        return $query->getResult();
    }

    public function countByOffer($offer)
    {
        $query = $this->_em->createQuery('SELECT COUNT(ou) FROM FDOfferBundle:Outcome ou JOIN ou.offer o WHERE ou.offer = :offer ORDER BY o.date');
        $query->setParameter("offer", $offer);
        return $query->getResult();
    }

    public function findOfferOfTheDayDistinct($date)
    {
        $query = $this->_em->createQuery('SELECT DISTINCT IDENTITY(ou.offer) FROM FDOfferBundle:Outcome ou JOIN ou.offer o WHERE o.date LIKE :date ORDER BY o.sportId');
        $query->setParameter('date', '%'.$date.'%');
        return $query->getResult();
    }
}