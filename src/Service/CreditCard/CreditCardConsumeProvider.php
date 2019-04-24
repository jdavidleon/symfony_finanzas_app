<?php


namespace App\Service\CreditCard;


use App\Entity\CreditCard\CreditCard;
use App\Entity\CreditCard\CreditCardUser;
use App\Entity\Security\User;
use App\Repository\CreditCard\CreditCardConsumeRepository;

class CreditCardConsumeProvider
{

    /**
     * @var CreditCardConsumeRepository
     */
    private $cardConsumeRepository;

    public function __construct(
        CreditCardConsumeRepository $cardConsumeRepository
    )
    {
        $this->cardConsumeRepository = $cardConsumeRepository;
    }
    /**
     * @param User $owner
     * @param null $month
     * @return array
     */
    public function getByOwner(User $owner, $month = null)
    {
        return $this->cardConsumeRepository->getByOwner($owner, $month);
    }

    public function getByCreditCard(CreditCard $card, $month = null)
    {
        return $this->cardConsumeRepository->getByCreditCard( $card, $month );
    }

    public function getByCardUser(CreditCardUser $user, CreditCard $card = null, $month = null)
    {
        return $this->cardConsumeRepository->getByCardUser($user, $card, $month);
    }

    public function getCreatedConsumeList()
    {
        return $this->cardConsumeRepository->findCreatedConsumeList();
    }

}