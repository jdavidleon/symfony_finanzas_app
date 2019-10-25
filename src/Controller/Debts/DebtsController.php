<?php

namespace App\Controller\Debts;

use App\Entity\Debts\Creditor;
use App\Entity\Debts\Credits;
use App\Entity\Debts\FixedCharges;
use App\Extractor\Debt\DebtsExtractor;
use App\Form\Debts\CreditorType;
use App\Form\Debts\DebtType;
use App\Form\Debts\FixedChargesType;
use App\Service\Debts\DebtsHandlers;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/debts")
 *
 * */
class DebtsController extends AbstractController
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
     * @param DebtsExtractor $debtsExtractor
     * @return Response
     * @throws Exception
     */
    public function debtsLists(DebtsExtractor $debtsExtractor)
    {
        $debts = $debtsExtractor->getNextDebtsByUser($this->getUser());

        return $this->render('::base.html.twig', [
            'debts' => $debts
        ]);
    }

    /**
     * @Route("/new", name="debts_debts_contrtoller")
     * @param DebtsHandlers $debtsHandlers
     * @return Response
     * @throws Exception
     */
    public function newDebt(DebtsHandlers $debtsHandlers)
    {
        $debt = new Credits();
        $debt->setUser($this->getUser());
        $form = $this->createForm(DebtType::class, $debt);

        if ( $form->isSubmitted() && $form->isValid() ){
            $em = $this->getDoctrine()->getManager();
            $em->persist($debt);
            $em->flush();

            $debtsHandlers->setBalanceDebt($debt);
        }

        return $this->render('debts/debts_contrtoller/index.html.twig', [
            'controller_name' => 'DebtsController',
        ]);
    }

    /**
     * @return Response
     * @throws Exception
     */
    public function newFixedCharges()
    {
        $fixedCharges = new FixedCharges();
        $fixedCharges->setUser($this->getUser());
        $form = $this->createForm(FixedChargesType::class, $fixedCharges);

        if ($form->isSubmitted() && $form->isValid()){
            $em = $this->getDoctrine()->getManager();
            $em->persist($fixedCharges);
            $em->flush();
        }

        return $this->render('debts/debts_contrtoller/index.html.twig', [
            'controller_name' => 'DebtsController',
        ]);
    }

    public function newDebtPayment()
    {

    }

    /**
     *
     * @Route("/creditors/new", name="new_creditor")
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
