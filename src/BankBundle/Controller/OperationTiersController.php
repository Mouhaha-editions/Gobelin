<?php

namespace BankBundle\Controller;

use BankBundle\Entity\Account;
use BankBundle\Entity\OperationTiers;
use BankBundle\Form\AccountType;
use BankBundle\Form\OperationTiersType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Account controller.
 *
 */
class OperationTiersController extends Controller
{
    /**
     * Lists all account entities.
     *
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('BankBundle:OperationTiers')->createQueryBuilder('t')->orderBy('t.label','asc')->getQuery()->getResult();

        return $this->render('@Bank/Tiers/index.html.twig', array(
            'entities' => $entities,
        ));
    }

    /**
     * Creates a new account entity.
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function newAction(Request $request)
    {
        $entity = new OperationTiers();
     return $this->editAction($request, $entity);
    }

    /**
     * Displays a form to edit an existing account entity.
     * @param Request $request
     * @param OperationTiers $entity
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function editAction(Request $request, OperationTiers $entity)
    {
        $editForm = $this->createForm(OperationTiersType::class, $entity);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $this->getDoctrine()->getManager()->persist($entity);
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('bank_operation_tiers_index');
        }

        return $this->render('@Bank/Tiers/edit.html.twig', array(
            'entity' => $entity,
            'form' => $editForm->createView(),
        ));
    }
    public function jsonListAction(Request $request)
    {
        $entities = $this->getDoctrine()->getRepository('BankBundle:OperationTiers')->createQueryBuilder('ot')
            ->select('ot.label, ot.id')
            ->orderBy('ot.label', 'ASC')
            ->getQuery()->getResult();

        return new JsonResponse($entities);
    }

}
