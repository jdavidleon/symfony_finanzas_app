<?php

namespace App\Controller\Debts;

use App\Entity\Debts\Creditor;
use App\Entity\Debts\Debt;
use App\Form\Debts\CreditorType;
use App\Form\Debts\DebtType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/debts")
 *
 * */
class DebtsController extends Controller
{
    /**
     * @Route("/", name="debts_debts_contrtoller")
     */
    public function index()
    {
        return $this->render('debts/debts_contrtoller/index.html.twig', [
            'controller_name' => 'DebtsController',
        ]);
    }

    /**
     * @Route("/list", name="debts_debts_contrtoller")
     */
    public function debtsLists()
    {
        $repo = $this->getDoctrine()->getRepository(Debt::class);

        $debts = $repo->getActualDebtsByUser($this->getUser());

        return $this->render('::base.html.twig', [
            'debts' => $debts
        ]);
    }

    /**
     * @Route("/new", name="debts_debts_contrtoller")
     * @throws \Exception
     */
    public function newDebt()
    {
        $debt = new Debt();
        $debt->setUser($this->getUser());
        $form = $this->createForm(DebtType::class, $debt);

        if ( $form->isSubmitted() && $form->isValid() ){
            $em = $this->getDoctrine()->getManager();

            $em->persist($debt);
            $em->flush();
        }

        return $this->render('debts/debts_contrtoller/index.html.twig', [
            'controller_name' => 'DebtsController',
        ]);
    }

    /**
     *
     * @Route("/creditors/new", name="new-creditor")
     *
     * */
    public function newCreditor()
    {
        $creditor = new Creditor();

        $form = $this->createForm(CreditorType::class, $creditor);

        if ( $form->isSubmitted() && $form->isValid() ){
            $em = $this->getDoctrine()->getManager();

            $em->persist($creditor);
            $em->flush();
        }

        return $this->render('debts/debts_contrtoller/index.html.twig', [
            'controller_name' => 'DebtsController',
        ]);

    }
    
}
