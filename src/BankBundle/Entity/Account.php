<?php

namespace BankBundle\Entity;

use BankBundle\Controller\OperationCategoryController;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="account")
 * @ORM\Entity(repositoryClass="BankBundle\Repository\AccountRepository")
 */
class Account
{
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
     * @var string
     *
     * @ORM\Column(name="description", type="text", nullable=true)
     */
    private $description;

    /**
     * @var Operation[]|ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="BankBundle\Entity\Operation",mappedBy="account")
     */
    private $operations;

    /**
     * @var Recurrence[]|ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="BankBundle\Entity\Recurrence",mappedBy="account")
     */
    private $recurrences ;

    public function __construct()
    {
        $this->operations = new ArrayCollection();
        $this->recurrences = new ArrayCollection();
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
     * @return Account
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
     * Set description.
     *
     * @param string|null $description
     *
     * @return Account
     */
    public function setDescription($description = null)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description.
     *
     * @return string|null
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Add operation.
     *
     * @param \BankBundle\Entity\Operation $operation
     *
     * @return Account
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
     * @return Operation[]
     */
    public function getOperations()
    {
        return $this->operations;
    }

    /**
     * Add recurrence.
     *
     * @param \BankBundle\Entity\Recurrence $recurrence
     *
     * @return Account
     */
    public function addRecurrence(Recurrence $recurrence)
    {
        $this->recurrences[] = $recurrence;

        return $this;
    }

    /**
     * Remove recurrence.
     *
     * @param \BankBundle\Entity\Recurrence $recurrence
     *
     * @return boolean TRUE if this collection contained the specified element, FALSE otherwise.
     */
    public function removeRecurrence(Recurrence $recurrence)
    {
        return $this->recurrences->removeElement($recurrence);
    }

    /**
     * Get recurrences.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getRecurrences()
    {
        return $this->recurrences;
    }
}
