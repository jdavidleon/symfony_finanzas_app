<?php

namespace App\Controller;

use App\Entity\CreditCard\CreditCard;
use App\Entity\CreditCard\CreditCardConsume;
use App\Entity\CreditCard\CreditCardUser;
use App\Form\Credit\CreditCardType;
use App\Form\Credit\CreditCardUserType;
use App\Form\Credit\CreditConsumeType;
use App\Repository\CreditCard\CreditCardConsumeRepository;
use App\Service\CreditCard\CreditCalculations;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;


/**
 * @Route("/credits")
 * */
class CreditController extends Controller
{

    /**
     * @var CreditCardConsumeRepository
     */
    private $creditCardConsumeRepository;

    public function __construct(
        CreditCardConsumeRepository $creditCardConsumeRepository
    )
    {
        $this->creditCardConsumeRepository = $creditCardConsumeRepository;
    }

    /**
     * @Route("/list", name="credit")
     * @param CreditCalculations $creditCalculations
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function index(CreditCalculations $creditCalculations)
    {
        $creditConsume =  $this->creditCardConsumeRepository->findByUser(  $this->getUser() );

        $creditCardConsume = [];
        $actualPay = [];
        foreach ($creditConsume as $item){
            $creditCardConsume[] = $creditCalculations->getDuesToPay( $item );
            $actualPay[] = $creditCalculations->getNextPaymentAmount( $item ) ;
        }
        dump(  $creditCardConsume  , $actualPay); die;
        return $this->render('credit/index.html.twig', [
            'controller_name' => 'CreditController',
            'credit_consume' => $creditCardConsume,
            'actual_pay' => $actualPay
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
     * @Route("/credit-card-debt/{creditCard}", name="credit_card_debt")
     * @param CreditCalculations $creditCalculations
     * @param $creditCard
     */
    public function creditCardDetail(CreditCalculations $creditCalculations, $creditCard)
    {
        $creditCardDebts = $this->creditCardConsumeRepository->findByCreditCard( $creditCard );

        $debtsByUser = $creditCalculations->getCreditCardDebtsByUser( $creditCardDebts );
        $resumeByUsers = $creditCalculations->getDebtsByUserInCreditCard( $debtsByUser );

        dump($resumeByUsers);
        dump($debtsByUser); die;

    }

    /**
     * @Route("/alias", name="create_credit_card_user")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function createCreditCardUser(Request $request)
    {
        $creditCardUser = new CreditCardUser();
        $form = $this->createForm(CreditCardUserType::class, $creditCardUser);

        $form->handleRequest($request);

        if ( $form->isSubmitted() && $form->isValid() ){
            $creditCardUser->setParent( $this->getUser() );
            $em = $this->getDoctrine()->getManager();
            $em->persist($creditCardUser);
            $em->flush();

            $this->addFlash('success', 'Alias Creado');
            $this->redirectToRoute('credit_new');
        }

        return $this->render('credit/credit_card_user_new.html.twig', [
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
