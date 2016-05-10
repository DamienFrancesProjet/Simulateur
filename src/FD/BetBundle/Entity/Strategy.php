<?php

namespace FD\BetBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Offer
 *
 * @ORM\Table(name="str_strategy", indexes={
 *  @ORM\Index(name="index_strategy_id", columns={"STR_ID"}),
 *  @ORM\Index(name="index_strategy_label", columns={"STR_LABEL"})
 * })
 * @ORM\Entity(repositoryClass="FD\BetBundle\Repository\StrategyRepository")
 */
class Strategy
{
    /**
     * @var int
     *
     * @ORM\Column(name="STR_ID", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="STR_LABEL", type="string", length=255)
     */
    private $label;

    /**
     * @var float
     *
     * @ORM\Column(name="STR_MONEY_SPENT", type="float", nullable=true)
     */
    private $moneySpent;

    /**
     * @var boolean
     *
     * @ORM\Column(name="STR_WAITING", type="boolean", nullable=true)
     */
    private $waiting;

    /**
     * @var float
     *
     * @ORM\Column(name="STR_MONEY_EARNED", type="float", nullable=true)
     */
    private $moneyEarned;

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
     * @return float
     */
    public function getMoneySpent()
    {
        return $this->moneySpent;
    }

    /**
     * @param float $moneySpent
     */
    public function setMoneySpent($moneySpent)
    {
        $this->moneySpent = $moneySpent;
    }

    /**
     * @return float
     */
    public function getMoneyEarned()
    {
        return $this->moneyEarned;
    }

    /**
     * @param float $moneyEarned
     */
    public function setMoneyEarned($moneyEarned)
    {
        $this->moneyEarned = $moneyEarned;
    }

    /**
     * @return mixed
     */
    public function getWaiting()
    {
        return $this->waiting;
    }

    /**
     * @param mixed $waiting
     */
    public function setWaiting($waiting)
    {
        $this->waiting = $waiting;
    }
}


