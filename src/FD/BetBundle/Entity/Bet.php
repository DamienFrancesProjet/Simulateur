<?php

namespace FD\BetBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use FD\OfferBundle\Entity\Outcome;
use FD\ResultBundle\Entity\MarketResult;

/**
 * Bet
 *
 * @ORM\Table(name="bet_bet", indexes={
 *  @ORM\Index(name="index_bet_id", columns={"BET_ID"}),
 *  @ORM\Index(name="index_bet_outcome", columns={"BET_OUT_OUTCOME_ID"}),
 *  @ORM\Index(name="index_bet_market_result", columns={"BET_MAR_MARKET_RESULT_ID"})
 * })
 * @ORM\Entity(repositoryClass="FD\BetBundle\Repository\BetRepository")
 */
class Bet
{
    /**
     * @var int
     *
     * @ORM\Column(name="BET_ID", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var outcome
     *
     * @ORM\ManyToOne(targetEntity="FD\OfferBundle\Entity\Outcome")
     * @ORM\JoinColumn(name="BET_OUT_OUTCOME_ID", referencedColumnName="OUT_ID")
     */
    private $outcome;

    /**
     * @var marketResult
     *
     * @ORM\ManyToOne(targetEntity="FD\ResultBundle\Entity\MarketResult")
     * @ORM\JoinColumn(name="BET_MAR_MARKET_RESULT_ID", referencedColumnName="MAR_ID")
     */
    private $marketResult;

    /**
     * @var strategy
     *
     * @ORM\ManyToOne(targetEntity="FD\BetBundle\Entity\Strategy")
     * @ORM\JoinColumn(name="BET_STR_STRATEGY_ID", referencedColumnName="STR_ID")
     */
    private $strategy;

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
     * @return Outcome
     */
    public function getOutcome()
    {
        return $this->outcome;
    }

    /**
     * @param Outcome $outcome
     */
    public function setOutcome($outcome)
    {
        $this->outcome = $outcome;
    }

    /**
     * @return MarketResult
     */
    public function getMarketResult()
    {
        return $this->marketResult;
    }

    /**
     * @param MarketResult $marketResult
     */
    public function setMarketResult($marketResult)
    {
        $this->marketResult = $marketResult;
    }

    /**
     * @return strategy
     */
    public function getStrategy()
    {
        return $this->strategy;
    }

    /**
     * @param strategy $strategy
     */
    public function setStrategy($strategy)
    {
        $this->strategy = $strategy;
    }
}