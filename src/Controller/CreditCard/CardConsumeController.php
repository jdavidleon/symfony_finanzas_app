<?php


namespace App\Controller\CreditCard;


use App\Entity\CreditCard\CreditCard;
use App\Entity\CreditCard\CreditCardConsume;
use App\Entity\CreditCard\CreditCardUser;
use App\Extractor\CreditCard\CreditCardConsumeExtractor;
use App\Form\Credit\BasicPaymentType;
use App\Form\Credit\CreditPaymentType;
use App\Service\CreditCard\CreditCalculator;
use App\Service\CreditCard\CreditCardConsumeProvider;
use App\Service\Payments\PaymentHandler;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
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
                $consume->activatePayment();
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
     * @Route("/consume/{consume}", name="consume_detail")
     * @param CreditCardConsume $consume
     * @param CreditCardConsumeExtractor $consumeExtractor
     * @return Response
     * @throws Exception
     */
    public function consumeDetail(CreditCardConsume $consume, CreditCardConsumeExtractor $consumeExtractor)
    {
        $consumeDetail = $consumeExtractor->extractPendingPaymentsByConsume($consume);
//        $consumeResume = $consumeExtractor->extractConsumeResume([
//            $consume
//        ]);
//
//        dump($consumeResume);die;

        return $this->render('credit/consume_detail.html.twig', [
            'consume_detail' => $consumeDetail,
            'consume' => $consume,
        ]);
    }


    /**
     * @Route("/payment/{cardConsume}", name="pay_consume")
     * @param CreditCardConsume $cardConsume
     * @param CreditCardConsumeExtractor $consumeExtractor
     * @param PaymentHandler $handlePayment
     * @param Request $request
     * @return Response
     * @throws Exception
     */
    public function paymentAction(
        CreditCardConsume $cardConsume,
        CreditCardConsumeExtractor $consumeExtractor,
        PaymentHandler $handlePayment,
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

    /**
     * @Route("basic_payment/{card}/{user}")
     *
     * @param CreditCard $card
     * @param CreditCardUser $user
     * @param PaymentHandler $paymentsHandler
     * @return Response
     */
    public function basicPaymentOfCardAndUser(CreditCard $card, CreditCardUser $user, PaymentHandler $paymentsHandler)
    {
        $consumeRepo = $this->getDoctrine()->getRepository(CreditCardConsume::class);
        $consumes = $consumeRepo->getByCardAndUser($card, $user);


        $form = $this->createForm(BasicPaymentType::class, [
            'credit_card' => $card,
            'credit_card_user' => $user,
        ]);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $paymentsHandler->processAllPaymentsByCardAndUser($card, $user);
            } catch (Exception $e) {

            }

            return $this->redirectToRoute('');
        }


        return $this->render('', [
            'consumes' => $consumes
        ]);
    }

}