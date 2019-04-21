<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 6/04/2019
 * Time: 12:24 AM
 */

namespace App\Controller\CreditCard;


use App\Entity\CreditCard\CreditCard;
use App\Entity\CreditCard\CreditCardUser;
use App\Form\Credit\CreditCardType;
use App\Repository\CreditCard\CreditCardRepository;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/creditcard")
 * */
class CreditCardController extends Controller
{

    /**
     * @Route("/list", name="credit_card_list")
     * @param CreditCardRepository $creditCardRepository
     * @return Response
     */
    public function list(CreditCardRepository $creditCardRepository)
    {
        $creditCards = $creditCardRepository->findBy([
            'owner' => $this->getUser()
        ]);

        foreach ($creditCards as $card){
            foreach ($card->getCreditCardConsumes() as $consume){
                foreach ($consume->getPayments() as $payment){
                    dump($payment->getAmount());
                }
            }
        }

        dump($creditCards); die;
        return $this->renderView('base.html.twig', [
            'credit_cards' => $creditCards
        ]);
    }

    /**
     * @Route("/new", name="credit_card_new")
     * @param Request $request
     * @return Response
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
            $this->redirectToRoute('credit_list');
        }

        return $this->render('credit/credit_card_new.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/card/{card}", name="credit_card_detail")
     *
     * @param CreditCard $card
     * @return string
     */
    public function creditCardDetail(CreditCard $card)
    {
        $repo = $this->getDoctrine()->getRepository(CreditCardUser::class);

        $cardUsers = $repo->getByCreditCard($card);

        return $this->render('credit/credit_card_detail.html.twig',[
            'card' => $card,
            'card_users' => $cardUsers
        ]);
    }
}