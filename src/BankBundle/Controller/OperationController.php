<?php

namespace BankBundle\Controller;

use BankBundle\Entity\Account;
use BankBundle\Entity\Operation;
use BankBundle\Entity\OperationCategory;
use BankBundle\Entity\OperationTiers;
use BankBundle\Entity\Recurrence;
use BankBundle\Form\OperationType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\VarDumper\VarDumper;

/**
 * Account controller.
 *
 */
class OperationController extends Controller
{
    /**
     * Lists all account entities.
     * @param Request $request
     * @param Account $account
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function indexAction(Request $request, Account $account)
    {
        $data = [
            'startDate' => (new \DateTime())->modify('first day of this month'),
            'endDate' => (new \DateTime())->modify('last day of this month'),
            'pointed' => true
        ];
        $form = $this->createFormBuilder($data, ['method' => 'get']);
        $form->add('startDate', DateType::class, [
            'format' => DateType::HTML5_FORMAT,
            'widget' => 'single_text',
            'required' => true,
            'attr' => ['placeholder' => "recurrence.placeholder.startDate"],
            'label' => 'recurrence.label.startDate'
        ])
            ->add('endDate', DateType::class, [
                'format' => DateType::HTML5_FORMAT,
                'widget' => 'single_text',
                'required' => false,
                'attr' => ['placeholder' => "recurrence.placeholder.endDate"],
                'label' => 'recurrence.label.endDate'
            ])
            ->add('pointed', CheckboxType::class, [
                'required'=>false,
                'attr' => ['placeholder' => "operation.placeholder.pointed"],
                'label' => 'operation.label.pointed'])
            ->add('label', TextType::class, [
                'required'=>false,
                'attr' => ['placeholder' => "operation.placeholder.label"],
                'label' => 'operation.label.label'])
            ->add('voir', SubmitType::class);


        $form = $form->getForm();
        $form->handleRequest($request);
        $start = $form->get('startDate')->getData();
        $end = $form->get('endDate')->getData();

        $this->get('bank.account')->generateRecurrencesForAccount($account, $end);

        $em = $this->getDoctrine()->getManager();

        $qb = $em->getRepository('BankBundle:Operation')->createQueryBuilder('o')
            ->where('o.account = :account')
            ->andWhere('o.deleted = :false')
            ->andWhere('(o.date BETWEEN :start AND :end OR  (o.date < :end AND o.pointed = 0))')
            ->setParameter('false', false)
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ->setParameter('account', $account)
            ->orderBy('o.date', 'DESC');
        if ($form->get('label')->getNormData()) {
            $qb->andWhere('o.label LIKE :thelabel')
                ->setParameter('thelabel', '%' . $form->get('label')->getNormData(). '%');
        }
        if ($form->get('pointed')->getNormData() == null) {
            $qb->andWhere('o.pointed = 0');
        }


        $pagination = $this->get('pkshetlie.pagination')->process($qb, $request);

        /** @var Recurrence[] $recurrences */
        $recurrences = $em->getRepository('BankBundle:Recurrence')->createQueryBuilder('o')
            ->where('o.account = :account')
            ->setParameter('account', $account)
            ->orderBy('o.start_date', 'desc')
            ->getQuery()->getResult();
        return $this->render('@Bank/Operation/index.html.twig', array(
            'account' => $account,
            'form' => $form->createView(),
            'operations' => $pagination,
            'recurrences' => $recurrences,
        ));
    }


    public function statistiquesAction(Request $request, Account $account)
    {
        $data = [
            'startDate' => (new \DateTime())->modify('-1 months'),
            'endDate' => (new \DateTime())->modify('+1 months'),
        ];
        $form = $this->createFormBuilder($data, ['method' => 'get']);
        $form->add('startDate', DateType::class, [
            'format' => DateType::HTML5_FORMAT,
            'widget' => 'single_text',
            'required' => true,
            'attr' => ['placeholder' => "recurrence.placeholder.startDate"],
            'label' => 'recurrence.label.startDate'
        ])
            ->add('endDate', DateType::class, [
                'format' => DateType::HTML5_FORMAT,
                'widget' => 'single_text',
                'required' => false,
                'attr' => ['placeholder' => "recurrence.placeholder.endDate"],
                'label' => 'recurrence.label.endDate'
            ])
            ->add('voir', SubmitType::class);


        $form = $form->getForm();
        $form->handleRequest($request);
        $start = $form->get('startDate')->getData();
        $end = $form->get('endDate')->getData();

        $amountOutByCategory = $this->get('bank.account')->getAmountOutDateToDateByCategory($account, $start, $end);
        $amountInByCategory = $this->get('bank.account')->getAmountInDateToDateByCategory($account, $start, $end);

        $totalOut = array_sum(array_map(function ($r) {
            return $r['value'];
        }, $amountOutByCategory));

        $totalIn = array_sum(array_map(function ($r) {
            return $r['value'];
        }, $amountInByCategory));

        $ratio = -1 * $totalOut / ($totalIn == 0 ? 0.0000000001 : $totalIn) * 100;
        $amountOutDayByDay = $this->get('bank.account')->getAmountOutDayByDayDateToDate($account, $start, $end);
        $amountInDayByDay = $this->get('bank.account')->getAmountInDayByDayDateToDate($account, $start, $end);


        return $this->render('@Bank/Operation/stats.html.twig', [
            'form' => $form->createView(),
            'amountOutByCategory' => $amountOutByCategory,
            'amountInByCategory' => $amountInByCategory,
            'amountOutDayByDay' => $amountOutDayByDay,
            'amountInDayByDay' => $amountInDayByDay,
            'totalOut' => $totalOut,
            'totalIn' => $totalIn,
            'ratio' => $ratio,

        ]);
    }

    /**
     * Creates a new account entity.
     * @param Request $request
     * @param Account $account
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function newAction(Request $request, Account $account)
    {
        $operation = new Operation();
        $operation->setDate(new \DateTime());
        $operation->setAccount($account);
        $operation->setAmount(-10);

        return $this->editAction($request, $operation);
    }

    public function pointedAction(Operation $operation)
    {
        $em = $this->getDoctrine()->getManager();
        $operation->setPointed(!$operation->getPointed());
        $op = $this->get('bank.account')->budgetMethod($operation);
        $em->flush();

        return new JsonResponse(['operations' => $op != null ? [

            [
                'id' => $op->getId(),
                'amount' => number_format($op->getAmount(), 2, ',', ' '),
            ]
        ] : null
            ,
            'toAdd' => $operation->getPointed() ? $operation->getAmount() : $operation->getAmount() * -1,
        ]);
    }

    /**
     * Displays a form to edit an existing account entity.
     * @param Request $request
     * @param Operation $operation
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function editAction(Request $request, Operation $operation)
    {
        $form = $this->createForm(OperationType::class, $operation);
        $form->get('category')->setData($operation->getCategory() != null ? $operation->getCategory()->getId() : '');
        $form->get('category_text')->setData($operation->getCategory() ? $operation->getCategory()->getLabel() : '');
        $form->get('tiers')->setData($operation->getTiers() !== null ? $operation->getTiers()->getId() : '');
        $form->get('tiers_text')->setData($operation->getTiers() != null ? $operation->getTiers()->getLabel() : '');

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $id_category = $form->get('category')->getData();
            $category = $form->get('category_text')->getData();
            $id_tiers = $form->get('tiers')->getData();
            $tiers = $form->get('tiers_text')->getData();
            if ($id_category != null) {
                $category_entity = $this->getDoctrine()->getRepository('BankBundle:OperationCategory')->find($id_category);
            } else {
                $category_entity = $this->getDoctrine()->getRepository('BankBundle:OperationCategory')->findOneBy(['label' => $category]);
                if ($category_entity === null) {
                    $category_entity = new OperationCategory();
                    $category_entity->setLabel($category);
                    $this->getDoctrine()->getManager()->persist($category_entity);
                }
            }
            $operation->setCategory($category_entity);
            $category_entity->addOperation($operation);

            if ($id_tiers != null) {
                $tiers_entity = $this->getDoctrine()->getRepository('BankBundle:OperationTiers')->find($id_tiers);
            } else {
                $tiers_entity = $this->getDoctrine()->getRepository('BankBundle:OperationTiers')->findOneBy(['label' => $tiers]);
                if ($tiers_entity === null) {
                    $tiers_entity = new OperationTiers();
                    $tiers_entity->setLabel($tiers);
                    $this->getDoctrine()->getManager()->persist($tiers_entity);
                }
            }
            $operation->setTiers($tiers_entity);
            $tiers_entity->addOperation($operation);

            $this->getDoctrine()->getManager()->persist($operation);
            $this->getDoctrine()->getManager()->flush();
            $this->get('bank.account')->budgetMethod($operation);

            $this->addFlash('success', "Operation enregistrée");
            if ($request->get('add_one', false)) {
                return $this->redirectToRoute('bank_operation_new', ['id' => $operation->getAccount()->getId()]);
            } else {
                return $this->redirectToRoute('bank_operation_index', ['id' => $operation->getAccount()->getId()]);
            }
        }

        return $this->render('@Bank/Operation/edit.html.twig', array(
            'account' => $operation,
            'form' => $form->createView(),
        ));
    }

    public function deleteAction(Request $request, Operation $operation)
    {
        $account_id = $operation->getAccount()->getId();
        try {
            if ($operation->getRecurrence() !== null) {
                $operation->setDeleted(true);
            } else {
                $this->getDoctrine()->getManager()->remove($operation);
            }
            $this->get('bank.account')->budgetMethod($operation, true);
            $this->getDoctrine()->getManager()->flush();
            $this->addFlash('success', "Operation supprimée");
        } catch (\Exception $e) {
            $this->addFlash('danger', "Erreur");
        }

        return $this->redirectToRoute('bank_operation_index', ['id' => $account_id]);
    }

}
