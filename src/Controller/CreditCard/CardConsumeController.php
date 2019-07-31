<?php


namespace App\Controller\CreditCard;


use App\Entity\CreditCard\CreditCardConsume;
use App\Entity\CreditCard\CreditCardPayments;
use App\Entity\CreditCard\CreditCardUser;
use App\Extractor\CreditCard\CreditCardConsumeExtractor;
use App\Form\Credit\CreditPaymentType;
use App\Service\CreditCard\CreditCardConsumeProvider;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/consume")
 */
class CardConsumeController extends AbstractController
{
    public function __construct()
    {
    }

    /**
     * @Route("/activate/{consume}", name="activate_consume")
     * @param CreditCardConsume $consume
     * @return RedirectResponse
     */
    public function activateConsumeAction(CreditCardConsume $consume)
    {
        try{
            $consume->activate();
            $em = $this->getDoctrine()->getManager();
            $em->persist($consume);
            $em->flush();
        }catch (Exception $e){

        }

        return $this->redirectToRoute('credit_list');
    }


    /**
     * @Route("/user/{cardUser}", name="consume_user")
     * @param CreditCardUser $cardUser
     * @param CreditCardConsumeProvider $consumeProvider
     * @param CreditCardConsumeExtractor $consumeExtractor
     * @return Response
     */
    public function userConsumeAction(
        CreditCardUser $cardUser,
        CreditCardConsumeProvider $consumeProvider,
        CreditCardConsumeExtractor $consumeExtractor
    )
    {
        $consumes = $consumeProvider->getByCardUser($cardUser);
        $consumes = $consumeExtractor->extractConsumeListBy($consumes);

        return $this->render('credit/card_user.html.twig', [
            'consumes' => $consumes
        ]);
    }


    /**
     * @Route("/payment/{cardConsume}")
     * @param CreditCardConsume $cardConsume
     * @param CreditCardConsumeExtractor $consumeExtractor
     * @return Response
     */
    public function paymentAction(
        CreditCardConsume $cardConsume,
        CreditCardConsumeExtractor $consumeExtractor
    )
    {
        $payment = new CreditCardPayments();
        $payment->setAmount($consumeExtractor->extractNextPaymentAmount($cardConsume));

        $form = $this->createForm(CreditPaymentType::class, $payment);

        return $this->render('', [
            'form' => $form->createView()
        ]);
    }

}