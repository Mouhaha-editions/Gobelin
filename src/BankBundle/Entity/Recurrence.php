<?php

namespace BankBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 *
 *
 * @ORM\Table(name="recurrence")
 * @ORM\Entity(repositoryClass="BankBundle\Repository\RecurrenceRepository")
 */
class Recurrence
{
    const TYPE_DAY = 100;
    const TYPE_MONTH = 200;
    const TYPE_YEAR = 300;

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="label", type="string",length=200, nullable=false)
     */
    private $label;

    /**
     * @var integer
     *
     * @ORM\Column(name="the_each", type="integer",nullable=false)
     */
    private $each;

    /**
     * @var integer
     *
     * @ORM\Column(name="the_type", type="integer", nullable=false)
     */
    private $type;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="start_date", type="date", nullable=true)
     */
    private $start_date;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="end_date", type="date", nullable=true)
     */
    private $end_date;

    /**
     * @var double
     *
     * @ORM\Column(name="amount", type="decimal",scale=2,precision=10, nullable=false)
     */
    private $amount;

    /**
     * @var Account
     *
     * @ORM\ManyToOne(targetEntity="BankBundle\Entity\Account",inversedBy="recurrences")
     * @ORM\JoinColumn(name="account_id",nullable=false)
     */
    private $account;

    /**
     * @var OperationCategory
     *
     * @ORM\ManyToOne(targetEntity="BankBundle\Entity\OperationCategory",inversedBy="recurrences")
     * @ORM\JoinColumn(name="operation_category_id",nullable=false)
     */
    private $category;

    /**
     * @var OperationTiers
     *
     * @ORM\ManyToOne(targetEntity="BankBundle\Entity\OperationTiers",inversedBy="recurrences")
     * @ORM\JoinColumn(name="operation_tiers_id",nullable=false)
     */
    private $tiers;

    /**
     * @var boolean
     *
     * @ORM\Column(name="is_budget", type="boolean", nullable=false)
     */
    private $budget = false;

    /**
     * @var Operation[]|ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="BankBundle\Entity\Operation",mappedBy="recurrence")
     */
    private $operations;

    public function __construct()
    {
        $this->operations = new ArrayCollection();
    }

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set label.
     *
     * @param string $label
     *
     * @return Recurrence
     */
    public function setLabel($label)
    {
        $this->label = $label;

        return $this;
    }

    /**
     * Get label.
     *
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * Set each.
     *
     * @param int $each
     *
     * @return Recurrence
     */
    public function setEach($each)
    {
        $this->each = $each;

        return $this;
    }

    /**
     * Get each.
     *
     * @return int
     */
    public function getEach()
    {
        return $this->each;
    }

    /**
     * Set type.
     *
     * @param integer $type
     *
     * @return Recurrence
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type.
     *
     * @return \int
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set startDate.
     *
     * @param \DateTime|null $startDate
     *
     * @return Recurrence
     */
    public function setStartDate($startDate = null)
    {
        $this->start_date = $startDate;

        return $this;
    }

    /**
     * Get startDate.
     *
     * @return \DateTime|null
     */
    public function getStartDate()
    {
        return $this->start_date;
    }

    /**
     * Set endDate.
     *
     * @param \DateTime|null $endDate
     *
     * @return Recurrence
     */
    public function setEndDate($endDate = null)
    {
        $this->end_date = $endDate;

        return $this;
    }

    /**
     * Get endDate.
     *
     * @return \DateTime|null
     */
    public function getEndDate()
    {
        return $this->end_date;
    }

    /**
     * Set amount.
     *
     * @param string $amount
     *
     * @return Recurrence
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;

        return $this;
    }

    /**
     * Get amount.
     *
     * @return string
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * Set account.
     *
     * @param \BankBundle\Entity\Account $account
     *
     * @return Recurrence
     */
    public function setAccount(Account $account = null)
    {
        $this->account = $account;

        return $this;
    }

    /**
     * Get account.
     *
     * @return \BankBundle\Entity\Account
     */
    public function getAccount()
    {
        return $this->account;
    }

    /**
     * Set category.
     *
     * @param \BankBundle\Entity\OperationCategory $category
     *
     * @return Recurrence
     */
    public function setCategory(OperationCategory $category = null)
    {
        $this->category = $category;

        return $this;
    }

    /**
     * Get category.
     *
     * @return \BankBundle\Entity\OperationCategory
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * Set tiers.
     *
     * @param \BankBundle\Entity\OperationTiers $tiers
     *
     * @return Recurrence
     */
    public function setTiers(OperationTiers $tiers = null)
    {
        $this->tiers = $tiers;

        return $this;
    }

    /**
     * Get tiers.
     *
     * @return \BankBundle\Entity\OperationTiers
     */
    public function getTiers()
    {
        return $this->tiers;
    }

    /**
     * Add operation.
     *
     * @param \BankBundle\Entity\Operation $operation
     *
     * @return Recurrence
     */
    public function addOperation(Operation $operation)
    {
        $this->operations[] = $operation;

        return $this;
    }

    /**
     * Remove operation.
     *
     * @param \BankBundle\Entity\Operation $operation
     *
     * @return boolean TRUE if this collection contained the specified element, FALSE otherwise.
     */
    public function removeOperation(Operation $operation)
    {
        return $this->operations->removeElement($operation);
    }

    /**
     * Get operations.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getOperations()
    {
        return $this->operations;
    }

    /**
     * Set budget.
     *
     * @param bool $budget
     *
     * @return Recurrence
     */
    public function setBudget($budget)
    {
        $this->budget = $budget;

        return $this;
    }

    /**
     * Get budget.
     *
     * @return bool
     */
    public function getBudget()
    {
        return $this->budget;
    }
}
