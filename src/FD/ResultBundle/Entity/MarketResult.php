<?php
/**
 * Created by PhpStorm.
 * User: Damien
 * Date: 17/04/2016
 * Time: 19:07
 */

namespace FD\ResultBundle\Entity;

use Doctrine\ORM\Mapping as ORM;



/**
 * MarketResult
 *
 * @ORM\Table(name="mar_market_result", indexes={
 *  @ORM\Index(name="index_market_result_result", columns={"MAR_ID"}),
 *  @ORM\Index(name="index_market_result_id", columns={"MAR_RES_RESULT_ID"}),
 *  @ORM\Index(name="index_market_result_resultat", columns={"MAR_RESULTAT"})
 * })
 * @ORM\Entity(repositoryClass="FD\ResultBundle\Repository\MarketResultRepository")
 */
class MarketResult
{
    /**
     * @var int
     *
     * @ORM\Column(name="MAR_ID", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var int
     *
     * @ORM\Column(name="MAR_FDJ_NUMBER", type="integer")
     */
    private $fDJNumber;

    /**
     * @var string
     *
     * @ORM\Column(name="MAR_MARKET_TYPE", type="string", length=255)
     */
    private $marketType;

    /**
     * @var string
     *
     * @ORM\Column(name="MAR_RESULTAT", type="string", length=1)
     */
    private $resultat;

    /**
     * @var result
     *
     * @ORM\ManyToOne(targetEntity="FD\ResultBundle\Entity\Result")
     * @ORM\JoinColumn(name="MAR_RES_RESULT_ID", referencedColumnName="RES_ID")
     */
    private $result;

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
     * @return string
     */
    public function getResultat()
    {
        return $this->resultat;
    }

    /**
     * @param string $resultat
     */
    public function setResultat($resultat)
    {
        $this->resultat = $resultat;
    }

    /**
     * @return result
     */
    public function getResult()
    {
        return $this->result;
    }

    /**
     * @param result $result
     */
    public function setResult($result)
    {
        $this->result = $result;
    }
}