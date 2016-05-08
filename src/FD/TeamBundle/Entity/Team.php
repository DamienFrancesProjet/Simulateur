<?php
/**
 * Created by PhpStorm.
 * User: Damien
 * Date: 18/04/2016
 * Time: 01:07
 */

namespace FD\TeamBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Team
 *
 * @ORM\Table(name="tea_team", indexes={
 *  @ORM\Index(name="index_team_id", columns={"TEA_ID"}),
 *  @ORM\Index(name="index_team_label", columns={"TEA_LABEL"}),
 *  @ORM\Index(name="index_team_competition_id", columns={"TEA_COMPETITION_ID"}),
 *  @ORM\Index(name="index_team_points", columns={"TEA_POINTS"}),
 *  @ORM\Index(name="index_team_serie", columns={"TEA_SERIE"})
 * })
 * @ORM\Entity(repositoryClass="FD\TeamBundle\Repository\TeamRepository")
 *
 */
class Team
{
    /**
     * @var int
     *
     * @ORM\Column(name="TEA_ID", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="TEA_LABEL", type="string", length=255)
     */
    private $label;

    /**
     * @var int
     *
     * @ORM\Column(name="TEA_COMPETITION_ID", type="integer")
     */
    private $competitionId;

    /**
     * @var int
     *
     * @ORM\Column(name="TEA_POINTS", type="integer")
     */
    private $points;

    /**
     * @var string
     *
     * @ORM\Column(name="TEA_SERIE", type="string", length=255)
     */
    private $serie;

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
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @param int $label
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

    /**
     * @return int
     */
    public function getPoints()
    {
        return $this->points;
    }

    /**
     * @param int $points
     */
    public function setPoints($points)
    {
        $this->points = $points;
    }

    /**
     * @return string
     */
    public function getSerie()
    {
        return $this->serie;
    }

    /**
     * @param string $serie
     */
    public function setSerie($serie)
    {
        $this->serie = $serie;
    }
}