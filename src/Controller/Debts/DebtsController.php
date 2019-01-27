<?php

namespace App\Controller\Debts;

use App\Entity\Debts\Debt;
use App\Form\Debts\DebtType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;

class DebtsController extends Controller
{
    /**
     * @Route("/debts/debts/contrtoller", name="debts_debts_contrtoller")
     */
    public function index()
    {
        return $this->render('debts/debts_contrtoller/index.html.twig', [
            'controller_name' => 'DebtsController',
        ]);
    }

    /**
     * @throws \Exception
     */
    public function newDebt()
    {
        $debt = new Debt();
        $this->createForm(DebtType::class);
    }
}
