<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 7/04/2019
 * Time: 6:10 PM
 */

namespace App\Controller\CreditCard;

use App\Entity\CreditCard\CreditCardUser;
use App\Extractor\CreditCard\CreditCardConsumeExtractor;
use App\Form\Credit\CreditCardUserType;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/carduser")
 * */
class CreditCardUserController extends AbstractController
{
    /**
     * @Route("/user/{user}")
     * @param $user
     * @param CreditCardConsumeExtractor $cardConsumeExtractor
     */
    public function creditConsumeUser($user, CreditCardConsumeExtractor $cardConsumeExtractor)
    {
//        $cardConsumeExtractor->get;
//        $this->getDoctrine()->getRepository(CreditCardConsume::class)->get
    }

    /**
     * @Route("/new", name="card_user_new")
     *
     * @param Request $request
     * @return Response
     * @throws Exception
     */
    public function new(Request $request)
    {
        $cardUser = new CreditCardUser();
        $cardUser->setParent($this->getUser());
        $form = $this->createForm(CreditCardUserType::class, $cardUser);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()){
            $em = $this->getDoctrine()->getManager();
            $em->persist($cardUser);
            $em->flush();

            $this->addFlash('success', 'Credit User created');
            $this->redirectToRoute('card_user_new');
        }

        return $this->render('credit/card_user_new.html.twig', [
            'form' => $form->createView()
        ]);
    }


}