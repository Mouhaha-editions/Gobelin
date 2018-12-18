<?php

namespace BankBundle\Controller;

use BankBundle\Entity\Account;
use BankBundle\Entity\Operation;
use BankBundle\Entity\OperationCategory;
use BankBundle\Entity\OperationTiers;
use BankBundle\Entity\Recurrence;
use BankBundle\Form\OperationType;
use BankBundle\Form\RecurrenceType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\VarDumper\VarDumper;

/**
 * Account controller.
 *
 */
class RecurrenceController extends Controller
{

    /**
     * Creates a new account entity.
     * @param Request $request
     * @param Account $account
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function newAction(Request $request, Account $account)
    {
        $recurrence = new Recurrence();
        $recurrence->setAccount($account);
        $recurrence->setStartDate(new \DateTime());
        $recurrence->setEndDate((new \DateTime())->modify('+1 years'));
        return $this->editAction($request, $recurrence);
    }

    /**
     * Displays a form to edit an existing account entity.
     * @param Request $request
     * @param Recurrence $recurrence
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function editAction(Request $request, Recurrence $recurrence)
    {
        $form = $this->createForm(RecurrenceType::class, $recurrence);
        $form->get('category')->setData($recurrence->getCategory() != null ? $recurrence->getCategory()->getId() : '');
        $form->get('category_text')->setData($recurrence->getCategory() ? $recurrence->getCategory()->getLabel() : '');
        $form->get('tiers')->setData($recurrence->getTiers() !== null ? $recurrence->getTiers()->getId() : '');
        $form->get('tiers_text')->setData($recurrence->getTiers() != null ? $recurrence->getTiers()->getLabel() : '');

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
                    $category_entity->setLabel(ucfirst(strtolower($category)));
                    $this->getDoctrine()->getManager()->persist($category_entity);
                }
            }
            $recurrence->setCategory($category_entity);
            $category_entity->addRecurrence($recurrence);

            if ($id_tiers != null) {
                $tiers_entity = $this->getDoctrine()->getRepository('BankBundle:OperationTiers')->find($id_tiers);
            } else {

                $tiers_entity = $this->getDoctrine()->getRepository('BankBundle:OperationTiers')->findOneBy(['label' => $tiers]);
                if ($tiers_entity === null) {
                    $tiers_entity = new OperationTiers();
                    $tiers_entity->setLabel(ucfirst(strtolower($tiers)));
                    $this->getDoctrine()->getManager()->persist($tiers_entity);
                }
            }
            $recurrence->setTiers($tiers_entity);
            $tiers_entity->addRecurrence($recurrence);

            if ($recurrence->getId() !== null) {
                $this->get('bank.account')->updateRecurrences($recurrence);
            }

            $this->getDoctrine()->getManager()->persist($recurrence);
            $this->getDoctrine()->getManager()->flush();

            $this->addFlash('success', "Récurrence enregistrée");
            if ($request->get('add_one', false)) {
                return $this->redirectToRoute('bank_recurrence_new', ['id' => $recurrence->getAccount()->getId()]);
            } else {
                return $this->redirectToRoute('bank_operation_index', ['id' => $recurrence->getAccount()->getId()]);
            }
        }

        return $this->render('@Bank/Recurrence/edit.html.twig', array(
            'account' => $recurrence,
            'form' => $form->createView(),
        ));
    }

    public function deleteAction(Request $request, Recurrence $recurrence)
    {
        $account_id = $recurrence->getAccount()->getId();
        $em = $this->getDoctrine()->getManager();
        try {
            /** @var Operation $operation */
            foreach ($recurrence->getOperations() AS $operation) {
                $operation->setRecurrence(null);
                if (!$operation->getPointed()) {
                    $em->remove($operation);
                }
            }
            $recurrence->setAccount(null);
            $recurrence->setCategory(null);
            $recurrence->setTiers(null);
            $em->remove($recurrence);
            $em->flush();
            $this->addFlash('success', "Récurrence supprimée");
        } catch (\Exception $e) {
            $this->addFlash('danger', "Erreur");
            VarDumper::dump($e);
            die;
        }
        return $this->redirectToRoute('bank_operation_index', ['id' => $account_id]);
    }

}
