<?php


namespace App\Controller\CreditCard;


use App\Entity\CreditCard\CreditCardConsume;
use App\Entity\CreditCard\CreditCardUser;
use App\Extractor\CreditCard\CreditCardConsumeExtractor;
use App\Service\CreditCard\CreditCardConsumeProvider;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
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
            $consume->setStatusToActivate();
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
        $consumes = $consumeExtractor->extractListConsumeBy($consumes, 'user');

        return $this->render('credit/card_user.html.twig', [
            'consumes' => $consumes
        ]);
    }

}