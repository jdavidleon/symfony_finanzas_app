<?php

namespace App\Controller\CreditCard;

use App\Entity\CreditCard\CreditCard;
use App\Entity\CreditCard\CreditCardConsume;
use App\Extractor\CreditCard\CreditCardConsumeExtractor;
use App\Form\Credit\CreditCardType;
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
     * @Route("/list", name="credit_list")
     * @param CreditCardConsumeExtractor $creditCardConsumeExtractor
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function index(CreditCardConsumeExtractor $creditCardConsumeExtractor)
    {
        $creditConsume =  $this->creditCardConsumeRepository->getCreditsCardConsumesByOwner(
            $this->getUser()
        );

        $creditCardConsume = [];
        $actualPay = [];
        foreach ($creditConsume as $item){
            $creditCardConsume[] = $creditCardConsumeExtractor->getPendingDuesToPay( $item );
            $actualPay[] = $creditCardConsumeExtractor->getNextPaymentAmount( $item ) ;
        }
        dump(  $creditCardConsume  , $actualPay, $creditCardConsumeExtractor->s); die;
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

        $form = $this->createForm(CreditConsumeType::class, $creditConsume, [
            'credit_card_user' => $this->getUser()
        ]);

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
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function creditCardDetail(CreditCalculations $creditCalculations, $creditCard)
    {
        $creditCardDebts = $this->creditCardConsumeRepository->findByCreditCard( $creditCard );

        $debtsByUser = $creditCalculations->getCreditCardDebtsByUser( $creditCardDebts );
        $resumeByUsers = $creditCalculations->getDebtsByUserInCreditCard( $debtsByUser );

        dump($resumeByUsers);
        dump($debtsByUser); die;

    }

//    public function userCreditCardResume()
//    {
//
//    }

}
