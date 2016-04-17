<?php
/**
 * Created by PhpStorm.
 * User: Damien
 * Date: 17/04/2016
 * Time: 19:07
 */

namespace FD\OfferBundle\Entity;

use Doctrine\ORM\Mapping as ORM;



/**
 * Outcome
 *
 * @ORM\Table(name="out_outcome")
 * @ORM\Entity(repositoryClass="FD\OfferBundle\Repository\OutcomeRepository")
 */
class Outcome
{
    /**
     * @var int
     *
     * @ORM\Column(name="OUT_ID", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var float
     *
     * @ORM\Column(name="OUT_COTE", type="float")
     */
    private $cote;

    /**
     * @var offer
     *
     * @ORM\ManyToOne(targetEntity="FD\OfferBundle\Entity\Offer")
     * @ORM\JoinColumn(name="OUT_OFF_OFFER_ID", referencedColumnName="OFF_ID")
     */
    private $offer;


    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return float
     */
    public function getCote()
    {
        return $this->cote;
    }

    /**
     * @param float $cote
     */
    public function setCote($cote)
    {
        $this->cote = $cote;
    }

    /**
     * @return mixed
     */
    public function getOffer()
    {
        return $this->offer;
    }

    /**
     * @param mixed $offer
     */
    public function setOffer($offer)
    {
        $this->offer = $offer;
    }
}