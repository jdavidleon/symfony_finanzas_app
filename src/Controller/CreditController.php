<?php

namespace App\Controller;

use App\Entity\CreditCard\CreditCard;
use App\Entity\CreditCard\CreditCardConsume;
use App\Form\Credit\CreditCardType;
use App\Form\Credit\CreditConsumeType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;


/**
 * @Route("/credits")
 * */
class CreditController extends Controller
{
    /**
     * @Route("/list", name="credit")
     */
    public function index()
    {
        return $this->render('credit/index.html.twig', [
            'controller_name' => 'CreditController',
        ]);
    }

    /**
     * @Route("/new", name="credit_new")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function createCreditConsume(Request $request)
    {
        $creditConsume = new CreditCardConsume();
        $creditConsume->setUser( $this->getUser() );

        $form = $this->createForm(CreditConsumeType::class, $creditConsume);

        $form->handleRequest($request);

        if ( $form->isSubmitted() && $form->isValid() )
        {
            $em = $this->getDoctrine()->getManager();
            $em->persist($creditConsume);
            $em->flush();

            $this->addFlash('success', 'Crédito agregado');
            $this->redirectToRoute('credit');
        }


        return $this->render('credit/new.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/credit-card-new", name="credit_card_new")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function createCreditCard(Request $request)
    {
        $creditCard = new CreditCard();
        $form = $this->createForm(CreditCardType::class, $creditCard);

        $form->handleRequest($request);

        if ( $form->isSubmitted() && $form->isValid() ){
            $em = $this->getDoctrine()->getManager();
            $em->persist($creditCard);
            $em->flush();

            $this->addFlash('success', 'Tarjeta de crédito creada');
            $this->redirectToRoute('credit');
        }

        return $this->render('credit/credit_card_new.html.twig', [
            'form' => $form->createView()
        ]);
    }
}
