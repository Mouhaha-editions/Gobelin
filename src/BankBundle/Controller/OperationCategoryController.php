<?php

namespace BankBundle\Controller;

use BankBundle\Entity\Account;
use BankBundle\Entity\OperationCategory;
use BankBundle\Form\AccountType;
use BankBundle\Form\OperationCategoryType;
use Entity\Category;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Account controller.
 *
 */
class OperationCategoryController extends Controller
{
    /**
     * Lists all account entities.
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('BankBundle:OperationCategory')->createQueryBuilder('c')->orderBy('c.label','asc')->getQuery()->getResult();

        return $this->render('@Bank/Category/index.html.twig', array(
            'entities' => $entities,
        ));
    }

    public function jsonListAction(Request $request)
    {
        $entities = $this->getDoctrine()->getRepository('BankBundle:OperationCategory')->createQueryBuilder('oc')
            ->select('oc.label, oc.id')
            ->orderBy('oc.label', 'ASC')
            ->getQuery()->getResult();

        return new JsonResponse($entities);
    }

    /**
     * Creates a new account entity.
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function newAction(Request $request)
    {
        $entity = new OperationCategory();
     return $this->editAction($request, $entity);
    }

    /**
     * Displays a form to edit an existing account entity.
     * @param Request $request
     * @param Category $entity
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function editAction(Request $request, OperationCategory $entity)
    {
        $editForm = $this->createForm(OperationCategoryType::class, $entity);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $this->getDoctrine()->getManager()->persist($entity);
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('bank_operation_category_index');
        }

        return $this->render('@Bank/Category/edit.html.twig', array(
            'entity' => $entity,
            'form' => $editForm->createView(),
        ));
    }

}
