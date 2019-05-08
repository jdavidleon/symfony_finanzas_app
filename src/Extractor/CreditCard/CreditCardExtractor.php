<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 7/04/2019
 * Time: 3:45 PM
 */

namespace App\Extractor\CreditCard;


use App\Entity\Security\User;
use App\Repository\CreditCard\CreditCardRepository;

class CreditCardExtractor
{
    /**
     * @var CreditCardRepository
     */
    private $cardRepository;

    public function __construct(
        CreditCardRepository $cardRepository
    )
    {
        $this->cardRepository = $cardRepository;
    }

    public function extractByOwner(User $owner)
    {
        return $this->cardRepository->findBy([
           'owner' => $owner 
        ]);
    }


}