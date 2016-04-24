<?php

namespace FD\OfferBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Offer
 *
 * @ORM\Table(name="off_offer", indexes={
 *  @ORM\Index(name="index_offer_eventId", columns={"OFF_EVENT_ID"}),
 *  @ORM\Index(name="index_offer_id", columns={"OFF_ID"})
 * })
 * @ORM\Entity(repositoryClass="FD\OfferBundle\Repository\OfferRepository")
 */
class Offer
{
    /**
     * @var int
     *
     * @ORM\Column(name="OFF_ID", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var int
     *
     * @ORM\Column(name="OFF_EVENT_ID", type="integer")
     */
    private $eventId;

    /**
     * @var int
     *
     * @ORM\Column(name="OFF_SPORT_ID", type="integer")
     */
    private $sportId;

    /**
     * @var int
     *
     * @ORM\Column(name="OFF_FDJ_NUMBER", type="integer")
     */
    private $fDJNumber;

    /**
     * @var string
     *
     * @ORM\Column(name="OFF_MARKET_TYPE", type="string", length=255)
     */
    private $marketType;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="OFF_DATE", type="date")
     */
    private $date;

    /**
     * @var string
     *
     * @ORM\Column(name="OFF_LABEL", type="string", length=255)
     */
    private $label;

    /**
     * @var int
     *
     * @ORM\Column(name="OFF_COMPETITION_ID", type="integer")
     */
    private $competitionId;

    /**
     * @return string
     */
    public function getLabel($label)
    {
        return $this->label;
    }

    /**
     * @param string $label
     */
    public function setLabel($label)
    {
        $this->label = $label;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return int
     */
    public function getEventId()
    {
        return $this->eventId;
    }

    /**
     * @param int $eventId
     */
    public function setEventId($eventId)
    {
        $this->eventId = $eventId;
    }

    /**
     * @return int
     */
    public function getSportId()
    {
        return $this->sportId;
    }

    /**
     * @param int $sportId
     */
    public function setSportId($sportId)
    {
        $this->sportId = $sportId;
    }

    /**
     * @return int
     */
    public function getFDJNumber()
    {
        return $this->fDJNumber;
    }

    /**
     * @param int $fDJNumber
     */
    public function setFDJNumber($fDJNumber)
    {
        $this->fDJNumber = $fDJNumber;
    }

    /**
     * @return string
     */
    public function getMarketType()
    {
        return $this->marketType;
    }

    /**
     * @param string $marketType
     */
    public function setMarketType($marketType)
    {
        $this->marketType = $marketType;
    }

    /**
     * @return int
     */
    public function getCompetitionId()
    {
        return $this->competitionId;
    }

    /**
     * @param int $competitionId
     */
    public function setCompetitionId($competitionId)
    {
        $this->competitionId = $competitionId;
    }

    /**
     * @return \DateTime
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * @param \DateTime $date
     */
    public function setDate($date)
    {
        $this->date = $date;
    }
}

