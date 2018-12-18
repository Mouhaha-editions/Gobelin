<?php

namespace BankBundle\Controller;

use BankBundle\Entity\Account;
use BankBundle\Form\AccountType;
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

        $accounts = $em->getRepository('BankBundle:Account')->findAll();

        return $this->render('@Bank/Account/index.html.twig', array(
            'accounts' => $accounts,
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
        $account = new Account();
     return $this->editAction($request, $account);
    }

    /**
     * Displays a form to edit an existing account entity.
     * @param Request $request
     * @param Account $account
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function editAction(Request $request, Account $account)
    {
        $editForm = $this->createForm(AccountType::class, $account);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $this->getDoctrine()->getManager()->persist($account);
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('bank_account_index');
        }

        return $this->render('@Bank/Account/edit.html.twig', array(
            'account' => $account,
            'form' => $editForm->createView(),
        ));
    }

}
