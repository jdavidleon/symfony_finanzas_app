<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 7/04/2019
 * Time: 6:10 PM
 */

namespace App\Controller\CreditCard;

use App\Entity\CreditCard\CreditCardConsume;
use App\Extractor\CreditCard\CreditCardConsumeExtractor;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/card_consume")
 * */
class CreditCardUserController extends Controller
{
    /**
     * @Route("/{user}")
     * @param $user
     * @param CreditCardConsumeExtractor $cardConsumeExtractor
     */
    public function creditConsumeUser($user, CreditCardConsumeExtractor $cardConsumeExtractor)
    {
//        $cardConsumeExtractor->get;
//        $this->getDoctrine()->getRepository(CreditCardConsume::class)->get
    }
}