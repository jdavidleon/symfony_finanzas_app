<?php


namespace App\Controller\CreditCard;


use App\Entity\CreditCard\CreditCardConsume;
use App\Entity\CreditCard\CreditCardPayments;
use App\Entity\CreditCard\CreditCardUser;
use App\Extractor\CreditCard\CreditCardConsumeExtractor;
use App\Form\Credit\CreditCardPaymentType;
use App\Service\CreditCard\CreditCardConsumeProvider;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/consume")
 */
class CardConsumeController extends Controller
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
            if  ($consume->getStatus() == CreditCardConsume::STATUS_CREATED){
                $consume->activate();
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
     * @Route("/payment/{cardConsume}", name="pay_consume")
     * @param CreditCardConsume $cardConsume
     * @param CreditCardConsumeExtractor $consumeExtractor
     * @param Request $request
     * @return Response
     */
    public function paymentAction(
        CreditCardConsume $cardConsume,
        CreditCardConsumeExtractor $consumeExtractor,
        Request $request
    )
    {
        dump($cardConsume);
        $payment = new CreditCardPayments();

        $isPost = $this->isPost($request);
        if (!$isPost){
            $payment->setAmount($consumeExtractor->extractNextPaymentAmount($cardConsume));
        }
        $form = $this->createForm(CreditCardPaymentType::class, $payment);

        if ($isPost){
            $form->handleRequest();
            $em = $this->getDoctrine()->getManager();
            $em->persist($payment);
            $em->flush();
        }

        return $this->render('credit/new_card_payment.html.twig', [
            'form' => $form->createView() ?? null
        ]);
    }

    /**
     * @param Request $request
     * @return bool
     */
    private function isPost(Request $request): bool
    {
        return $request->isMethod('POST');
    }

}