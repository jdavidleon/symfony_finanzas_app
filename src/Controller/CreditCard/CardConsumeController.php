<?php


namespace App\Controller\CreditCard;


use App\Entity\CreditCard\CreditCardConsume;
use App\Entity\CreditCard\CreditCardUser;
use App\Extractor\CreditCard\CreditCardConsumeExtractor;
use App\Form\Credit\CreditPaymentType;
use App\Service\CreditCard\CreditCalculator;
use App\Service\CreditCard\CreditCardConsumeProvider;
use App\Service\Payments\HandlePayment;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/consume")
 */
class CardConsumeController extends AbstractController
{
    /**
     * @Route("/activate/{consume}", name="activate_consume")
     * @param CreditCardConsume $consume
     * @param CreditCalculator $calculator
     * @return RedirectResponse
     */
    public function activateConsumeAction(CreditCardConsume $consume, CreditCalculator $calculator)
    {
        try{
            if  ($consume->getStatus() == CreditCardConsume::STATUS_CREATED){
                $consume->activate();
                $consume->setMonthFirstPay($calculator->calculateNextPaymentDate());
            }else{
                $consume->setStatus(CreditCardConsume::STATUS_CREATED);
            }
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
     * @throws Exception
     */
    public function userConsumeAction(
        CreditCardUser $cardUser,
        CreditCardConsumeProvider $consumeProvider,
        CreditCardConsumeExtractor $consumeExtractor
    )
    {
        $consumes = $consumeProvider->getByCardUser($cardUser);
        $consumes = $consumeExtractor->extractConsumeResume($consumes);

        return $this->render('credit/card_user.html.twig', [
            'consumes' => $consumes
        ]);
    }


    /**
     * @Route("/payment/{cardConsume}", name="pay_consume")
     * @param CreditCardConsume $cardConsume
     * @param CreditCardConsumeExtractor $consumeExtractor
     * @param HandlePayment $handlePayment
     * @param Request $request
     * @return Response
     * @throws Exception
     */
    public function paymentAction(
        CreditCardConsume $cardConsume,
        CreditCardConsumeExtractor $consumeExtractor,
        HandlePayment $handlePayment,
        Request $request
    )
    {
        $form = $this->createForm(CreditPaymentType::class, null, [
            'total_to_pay' => $consumeExtractor->extractNextPaymentAmount($cardConsume)
        ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()){
            try {
                $handlePayment->processPayment($cardConsume, $form->get('total_to_pay')->getData());

                $this->addFlash('success', 'Pago realizado con Exito');
            } catch (Exception $exception) {

            }
        }

        return $this->render('credit/new_card_payment.html.twig', [
            'form' => $form->createView()
        ]);
    }

}