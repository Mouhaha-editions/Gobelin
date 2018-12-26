<?php
/**
 * Created by PhpStorm.
 * User: pierr
 * Date: 05/12/2018
 * Time: 22:12
 */

namespace BankBundle\Service;


use BankBundle\Entity\Account;
use BankBundle\Entity\Operation;
use BankBundle\Entity\OperationCategory;
use BankBundle\Entity\Recurrence;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Validator\Constraints\Date;
use Symfony\Component\VarDumper\VarDumper;

class AccountService
{
    /**
     * @var EntityManager
     */
    private $em;

    public function __construct(EntityManager $entityManager)
    {
        $this->em = $entityManager;
    }

    public function listAccounts()
    {
       return  $this->em->getRepository('BankBundle:Account')->createQueryBuilder('a')
           ->orderBy('a.label', 'ASC')
           ->getQuery()->getResult();
    }

    public function budgetMethod(Operation $operation, $delete = false)
    {
        /** @var Operation $budget */
        $budget = $this->em->getRepository('BankBundle:Operation')
            ->createQueryBuilder('o')
            ->where('o.category = :categorie')
            ->andWhere('o.date >= :date')
            ->andWhere('o.budget = :true')
            ->setParameter('categorie', $operation->getCategory())
            ->setParameter('date', $operation->getDate())
            ->setParameter('true', true)
            ->orderBy('o.date', 'ASC')
            ->setMaxResults(1)
            ->setFirstResult(0)
            ->getQuery()->getOneOrNullResult();

        if ($budget != null) {
            if ($delete) {
                $budget->setAmount($budget->getAmount() + $operation->getAmount());
            } else if ($operation->getId() == null) {
                $budget->setAmount($budget->getAmount() - $operation->getAmount());
            }
        }
    }

    /**
     * @param Account $account
     * @return mixed
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getAmountPointed(Account $account)
    {
        $count = $this->em->getRepository('BankBundle:Operation')
            ->createQueryBuilder('o')
            ->select('SUM(o.amount)')
            ->where('o.pointed = :true')
            ->andWhere('o.account = :account')
            ->andWhere('(o.budget = :false OR o.amount < 0)')
            ->andWhere('o.deleted = :false')
            ->setParameter('account', $account)
            ->setParameter('true', true)
            ->setParameter('false', false)
            ->setMaxResults(1)
            ->setFirstResult(0)
            ->getQuery()->getOneOrNullResult();
        return array_pop($count);
    }

    /**
     * @param Account $account
     * @param \DateTime $dateTime
     * @return mixed
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getAmountToDate(Account $account, \DateTime $dateTime)
    {
        $count = $this->em->getRepository('BankBundle:Operation')
            ->createQueryBuilder('o')
            ->select('SUM(o.amount)')
            ->where('o.date <= :date')
            ->andWhere('o.account = :account')
            ->andWhere('(o.budget = :false OR o.amount < 0)')
            ->andWhere('o.deleted = :false')
            ->setParameter('account', $account)
            ->setParameter('date', $dateTime)
            ->setParameter('false', false)
            ->setMaxResults(1)
            ->setFirstResult(0)
            ->getQuery()->getOneOrNullResult();
        return array_pop($count);
    }

    public function generateRecurrencesForAccount(Account $account, \DateTime $to)
    {
        foreach ($account->getRecurrences() AS $recurrence) {
            $this->generateRecurrence($recurrence, $to);
        }
    }

    public function updateRecurrences(Recurrence $recurrence)
    {
        foreach ($recurrence->getOperations() AS $operation) {
            if (!$operation->getPointed()) {
                $operation->setTiers($recurrence->getTiers());
                $operation->setCategory($recurrence->getCategory());
                $operation->setLabel($recurrence->getLabel());
                $operation->setAccount($recurrence->getAccount());
                $operation->setRecurrence($recurrence);
                $operation->setAmount($recurrence->getAmount());
                $operation->setBudget($recurrence->getBudget());
                $this->em->flush();
            }
        }
    }

    public function generateRecurrence(Recurrence $recurrence, \DateTime $to)
    {
        $type = $recurrence->getType();
        $modify = "+1 Days";
        switch ($type) {
            case Recurrence::TYPE_DAY:
                $modify = '+' . $recurrence->getEach() . ' days';
                break;
            case Recurrence::TYPE_MONTH:
                $modify = '+' . $recurrence->getEach() . ' months';
                break;
            case Recurrence::TYPE_YEAR:
                $modify = '+' . $recurrence->getEach() . ' years';
                break;
        }
        $start = clone $recurrence->getStartDate();
        $end = clone $to;
        while ($start <= $end) {
            if ($recurrence->getEndDate() != null && $start > $recurrence->getEndDate()) {
                break;
            }
            $operation = $this->em->getRepository('BankBundle:Operation')
                ->createQueryBuilder('o')
                ->where('o.date = :thisone')
                ->setParameter('thisone', $start)
                ->setParameter('recurrence', $recurrence)
                ->andWhere('o.recurrence = :recurrence')
                ->getQuery()->getResult();
            if ($operation == null) {
                $operation = new Operation();
                $operation->setTiers($recurrence->getTiers());
                $operation->setCategory($recurrence->getCategory());
                $operation->setLabel($recurrence->getLabel());
                $operation->setAccount($recurrence->getAccount());
                $operation->setRecurrence($recurrence);
                $operation->setAmount($recurrence->getAmount());
                $operation->setBudget($recurrence->getBudget());
                $operation->setDate(clone $start);
                $this->em->persist($operation);
                $this->em->flush();
            }
            $start->modify($modify);
        }
    }

    /**
     * @param Account $account
     * @param $from
     * @param $to
     * @return array
     */
    public function getAmountOutDateToDateByCategory(Account $account, $from, $to)
    {
        return  $this->em->getRepository('BankBundle:Operation')
            ->createQueryBuilder('o')
            ->leftJoin('o.category', 'c')
            ->select('SUM(o.amount) AS value, c.label AS label')
            ->where('o.date <= :to')
            ->andWhere('o.date >=  :from')
            ->andWhere('o.amount < 0')
            ->andWhere('o.account = :account')
            ->andWhere('(o.budget = :false OR o.amount < 0)')
            ->andWhere('o.deleted = :false')
            ->setParameter('account', $account)
            ->setParameter('to', $to)
            ->setParameter('from', $from)
            ->setParameter('false', false)
            ->groupBy('c.id')
            ->getQuery()->getScalarResult();
    }

    /**
     * @param Account $account
     * @param $from
     * @param $to
     * @return array
     */
    public function getAmountInDateToDateByCategory(Account $account, $from, $to)
    {
        return  $this->em->getRepository('BankBundle:Operation')
            ->createQueryBuilder('o')
            ->leftJoin('o.category', 'c')
            ->select('SUM(o.amount) AS value, c.label AS label')
            ->where('o.date <= :to')
            ->andWhere('o.date >=  :from')
            ->andWhere('o.amount > 0')
            ->andWhere('o.account = :account')
            ->andWhere('(o.budget = :false OR o.amount < 0)')
            ->andWhere('o.deleted = :false')
            ->setParameter('account', $account)
            ->setParameter('to', $to)
            ->setParameter('from', $from)
            ->setParameter('false', false)
            ->groupBy('c.id')
            ->getQuery()->getScalarResult();
    }

    /**
     * @param Account $account
     * @param $from
     * @param $to
     * @return array
     */
    public function getAmountOutDayByDayDateToDate(Account $account, $from, $to)
    {
        return ($this->em->getRepository('BankBundle:Operation')
            ->createQueryBuilder('o')
            ->leftJoin('o.category', 'c')
            ->select('SUM(o.amount) AS value, o.date AS name')
            ->where('o.date <= :to')
            ->andWhere('o.date >=  :from')
            ->andWhere('o.amount < 0')
            ->andWhere('o.account = :account')
            ->andWhere('(o.budget = :false OR o.amount < 0)')
            ->andWhere('o.deleted = :false')
            ->setParameter('account', $account)
            ->setParameter('to', $to)
            ->setParameter('from', $from)
            ->setParameter('false', false)
            ->groupBy('o.date')
            ->orderBy('name', 'ASC')
            ->getQuery()->getScalarResult());
    }/**
     * @param Account $account
     * @param $from
     * @param $to
     * @return array
     */
    public function getAmountInDayByDayDateToDate(Account $account, $from, $to)
    {
        return  $this->em->getRepository('BankBundle:Operation')
            ->createQueryBuilder('o')
            ->leftJoin('o.category', 'c')
            ->select('SUM(o.amount) AS value, c.label AS name')
            ->where('o.date <= :to')
            ->andWhere('o.date >=  :from')
            ->andWhere('o.amount > 0')
            ->andWhere('o.account = :account')
            ->andWhere('(o.budget = :false OR o.amount < 0)')
            ->andWhere('o.deleted = :false')
            ->setParameter('account', $account)
            ->setParameter('to', $to)
            ->setParameter('from', $from)
            ->setParameter('false', false)
            ->groupBy('c.id')
            ->getQuery()->getScalarResult();
    }
}