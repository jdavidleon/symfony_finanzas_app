<?php


namespace App\Controller\CreditCard;


use App\Entity\CreditCard\CreditCard;
use App\Entity\CreditCard\CreditCardConsume;
use App\Entity\CreditCard\CreditCardUser;
use App\Exception\ExcedeAmountDebtException;
use App\Exception\MinimalAmountPaymentRequiredException;
use App\Extractor\CreditCard\CreditCardConsumeExtractor;
use App\Form\Credit\BasicPaymentType;
use App\Form\Credit\CreditPaymentType;
use App\Service\CreditCard\ConsumeResolver;
use App\Service\CreditCard\CreditCalculator;
use App\Service\CreditCard\CreditCardConsumeProvider;
use App\Service\Payments\PaymentHandler;
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
        try {
            if ($consume->getStatus() == CreditCardConsume::STATUS_CREATED) {
                $consume->activatePayment();
                $consume->setMonthFirstPay($calculator->calculateNextPaymentDate());
            } else {
                $consume->setStatus(CreditCardConsume::STATUS_CREATED);
            }
            $em = $this->getDoctrine()->getManager();
            $em->persist($consume);
            $em->flush();
        } catch (Exception $e) {
            // Todo: Y acá q???
        }

        return $this->redirectToRoute('credit_list');
    }


    /**
     * @Route("/user/{cardUser}", name="consume_user")
     * @param CreditCardUser $cardUser
     * @param CreditCardConsumeProvider $consumeProvider
     * @param CreditCardConsumeExtractor $consumeExtractor
     * @param ConsumeResolver $consumeResolver
     * @return Response
     * @throws Exception
     */
    public function userConsumeAction(
        CreditCardUser $cardUser,
        CreditCardConsumeProvider $consumeProvider,
        CreditCardConsumeExtractor $consumeExtractor,
        ConsumeResolver $consumeResolver
    ) {
        $consumesByUser = $consumeProvider->getActivesByCardUser($cardUser);
        $consumes = $consumeExtractor->extractConsumeResume($consumesByUser);

        $totalDebt = $consumeResolver->resolveTotalDebtOfConsumesArray($consumesByUser);
        $totalInterest = $consumeResolver->resolveTotalInterestToPayByConsumesArray($consumesByUser);

        return $this->render('credit/card_user.html.twig', [
            'consumes' => $consumes,
            'total_debt' => $totalDebt,
            'total_interest' => $totalInterest,
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
    ) {
        $form = $this->createForm(CreditPaymentType::class, null, [
            'total_to_pay' => $consumeExtractor->extractNextPaymentAmount($cardConsume)
        ]);

        $totalDebt = $consumeExtractor->extractActualDebt($cardConsume) + $consumeExtractor->extractNextInterestAmount($cardConsume);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $handlePayment->processPaymentWithSpecificAmount($cardConsume, $form->get('amount')->getData());
                $this->addFlash('success', 'Pago realizado con Éxito');
            } catch (MinimalAmountPaymentRequiredException $exception) {
                $this->addFlash('error', $exception->getMessage());
            } catch (ExcedeAmountDebtException $exception) {
                $this->addFlash('error', $exception->getMessage());
            } catch (\Exception $exception) {
                $this->addFlash('error', $exception->getMessage());
            }

            return $this->redirectToRoute('pay_consume', ['cardConsume' => $cardConsume->getId()]);
        }

        return $this->render('credit/new_card_payment.html.twig', [
            'form' => $form->createView(),
            'total_to_pay' => $totalDebt
        ]);
    }

    /**
     * @Route("/basic_payment/{card}/{user}", name="payment_by_card_and_user")
     *
     * @param CreditCard $card
     * @param CreditCardUser $user
     * @param PaymentHandler $paymentsHandler
     * @param CreditCardConsumeProvider $consumeProvider
     * @param Request $request
     * @return Response
     * @throws Exception
     */
    public function basicPaymentOfCardAndUser(
        CreditCard $card,
        CreditCardUser $user,
        PaymentHandler $paymentsHandler,
        CreditCardConsumeProvider $consumeProvider,
        Request $request
    ) {
        $consumes = $consumeProvider->getByCardAndUser($card, $user, true);

        $form = $this->createForm(BasicPaymentType::class, null, [
            'card' => $card,
            'card_user' => $user,
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $paymentsHandler->processAllPaymentsByCardAndUser($card, $user);
                $this->addFlash('success', 'Se han completado los pagos');
            } catch (Exception $e) {
                $this->addFlash('error', 'No se han procesados los pagos');
            }
            return $this->redirectToRoute('credit_card_detail', [
                'card' => $card->getId()
            ]);
        }

        return $this->render('credit/payment_card_and_user.html.twig', [
            'consumes' => $consumes,
            'form' => $form->createView()
        ]);
    }

}