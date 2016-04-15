<?php

namespace FD\OfferBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Offer
 *
 * @ORM\Table(name="offer")
 * @ORM\Entity(repositoryClass="FD\OfferBundle\Repository\OfferRepository")
 */
class Offer
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var int
     *
     * @ORM\Column(name="eventId", type="integer")
     */
    private $eventId;

    /**
     * @var int
     *
     * @ORM\Column(name="sportId", type="integer")
     */
    private $sportId;

    /**
     * @var int
     *
     * @ORM\Column(name="FDJNumber", type="integer")
     */
    private $fDJNumber;

    /**
     * @var string
     *
     * @ORM\Column(name="marketType", type="string", length=255)
     */
    private $marketType;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date", type="date")
     */
    private $date;

    /**
     * @var string
     *
     * @ORM\Column(name="offerName", type="string", length=255)
     */
    private $offerName;

    /**
     * @var int
     *
     * @ORM\Column(name="competitionId", type="integer")
     */
    private $competitionId;


    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set eventId
     *
     * @param integer $eventId
     *
     * @return Offer
     */
    public function setEventId($eventId)
    {
        $this->eventId = $eventId;

        return $this;
    }

    /**
     * Get eventId
     *
     * @return int
     */
    public function getEventId()
    {
        return $this->eventId;
    }

    /**
     * Set sportId
     *
     * @param integer $sportId
     *
     * @return Offer
     */
    public function setSportId($sportId)
    {
        $this->sportId = $sportId;

        return $this;
    }

    /**
     * Get sportId
     *
     * @return int
     */
    public function getSportId()
    {
        return $this->sportId;
    }

    /**
     * Set fDJNumber
     *
     * @param integer $fDJNumber
     *
     * @return Offer
     */
    public function setFDJNumber($fDJNumber)
    {
        $this->fDJNumber = $fDJNumber;

        return $this;
    }

    /**
     * Get fDJNumber
     *
     * @return int
     */
    public function getFDJNumber()
    {
        return $this->fDJNumber;
    }

    /**
     * Set marketType
     *
     * @param string $marketType
     *
     * @return Offer
     */
    public function setMarketType($marketType)
    {
        $this->marketType = $marketType;

        return $this;
    }

    /**
     * Get marketType
     *
     * @return string
     */
    public function getMarketType()
    {
        return $this->marketType;
    }

    /**
     * Set date
     *
     * @param \DateTime $date
     *
     * @return Offer
     */
    public function setDate($date)
    {
        $this->date = $date;

        return $this;
    }

    /**
     * Get date
     *
     * @return \DateTime
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * Set offerName
     *
     * @param string $offerName
     *
     * @return Offer
     */
    public function setOfferName($offerName)
    {
        $this->offerName = $offerName;

        return $this;
    }

    /**
     * Get offerName
     *
     * @return string
     */
    public function getOfferName()
    {
        return $this->offerName;
    }

    /**
     * Set competitionId
     *
     * @param integer $competitionId
     *
     * @return Offer
     */
    public function setCompetitionId($competitionId)
    {
        $this->competitionId = $competitionId;

        return $this;
    }

    /**
     * Get competitionId
     *
     * @return int
     */
    public function getCompetitionId()
    {
        return $this->competitionId;
    }
}

