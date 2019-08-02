<?php

namespace App\Controller\Debts;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class DebtsTypeController extends AbstractController
{
    /**
     * @Route("/debts/debts/type", name="debts_debts_type")
     */
    public function index()
    {
        return $this->render('debts/debts_type/index.html.twig', [
            'controller_name' => 'DebtsTypeController',
        ]);
    }
}
