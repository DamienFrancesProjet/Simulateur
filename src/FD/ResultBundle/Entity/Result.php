<?php
/**
 * Created by PhpStorm.
 * User: Damien
 * Date: 18/04/2016
 * Time: 01:07
 */

namespace FD\ResultBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Result
 *
 * @ORM\Table(name="res_result")
 * @ORM\Entity(repositoryClass="FD\ResultBundle\Repository\ResultRepository")
 *
 */
class Result
{
    /**
     * @var int
     *
     * @ORM\Column(name="RES_ID", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var int
     *
     * @ORM\Column(name="RES_EVENT_ID", type="integer")
     */
    private $eventId;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="RES_DATE", type="date")
     */
    private $date;

    /**
     * @var string
     *
     * @ORM\Column(name="RES_LABEL", type="string", length=255)
     */
    private $label;

    /**
     * @var int
     *
     * @ORM\Column(name="RES_COMPETITION_ID", type="integer")
     */
    private $competitionId;

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

    /**
     * @return string
     */
    public function getLabel()
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


}