<?php


namespace App\Service\CreditCard;


use App\Entity\CreditCard\CreditCard;
use App\Entity\CreditCard\CreditCardConsume;
use App\Entity\CreditCard\CreditCardUser;
use App\Entity\Security\User;
use App\Repository\CreditCard\CreditCardConsumeRepository;
use Exception;

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
        return $this->cardConsumeRepository->getActivesByOwner($owner, $month);
    }

    public function getByCreditCard(CreditCard $card, $month = null): array
    {
        return $this->cardConsumeRepository->getByCreditCard( $card, $month );
    }

    public function getByCardUser(CreditCardUser $user, CreditCard $card = null, $month = null)
    {
        return $this->cardConsumeRepository->getActivesByCardUser($user, $card, $month);
    }

    public function getCreatedConsumeListByOwner(User $owner)
    {
        return $this->cardConsumeRepository->findCreatedConsumeListByOwner($owner);
    }

    /**
     * @param CreditCard $card
     * @param CreditCardUser $cardUser
     * @param bool $excludeAlreadyPayedAtDate
     * @return CreditCardConsume[]
     * @throws Exception
     */
    public function getByCardAndUser(CreditCard $card, CreditCardUser $cardUser, bool $excludeAlreadyPayedAtDate = false)
    {
        $month = $excludeAlreadyPayedAtDate ? CreditCalculator::calculateNextPaymentDate() : null;

        return $this->cardConsumeRepository->getByCardAndUser($card, $cardUser, $month);
    }

}