<?php


namespace App\Service\CreditCard;


use App\Entity\CreditCard\CreditCard;
use App\Entity\CreditCard\CreditCardConsume;
use App\Entity\CreditCard\CreditCardPayment;
use App\Entity\CreditCard\CreditCardUser;
use App\Entity\Security\User;
use App\Repository\CreditCard\CreditCardConsumeRepository;
use App\Service\DateHelper;
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

    /**
     * @param CreditCardUser $user
     * @param CreditCard|null $card
     * @param bool $excludeAlreadyPayedAtDate
     * @return CreditCardConsume[]
     * @throws Exception
     */
    public function getActivesByCardUser(CreditCardUser $user, CreditCard $card = null, bool $excludeAlreadyPayedAtDate = false)
    {
        $month = $this->resolveExclusionMonth($excludeAlreadyPayedAtDate);

        return $this->cardConsumeRepository->getActivesByCardUser($user, $card, $month);
    }

    /**
     * @param CreditCardUser $user
     * @param CreditCard|null $card
     * @param bool $excludeAlreadyPayedAtDate
     * @return CreditCardConsume[]|array
     * @throws Exception
     */
    public function getAllByCardUser(CreditCardUser $user, CreditCard $card = null, bool $excludeAlreadyPayedAtDate = false): array
    {
        $consumes = $this->cardConsumeRepository->getByCardUser($user, $card);

        if ($excludeAlreadyPayedAtDate) {
            $filteredConsumes = [];
            foreach ($consumes as $consume) {
                if (!$consume->isConsumePayed() || $this->isRecentlyPayed($consume)) {
                    $filteredConsumes[] = $consume;
                    continue;
                }
            }
            return $filteredConsumes;
        }
        return $consumes;
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
        $month = $this->resolveExclusionMonth($excludeAlreadyPayedAtDate);

        return $this->cardConsumeRepository->getByCardAndUser($card, $cardUser, $month);
    }

    /**
     * @param bool $excludeAlreadyPayedAtDate
     * @return string|null
     * @throws Exception
     */
    private function resolveExclusionMonth(bool $excludeAlreadyPayedAtDate)
    {
        return $excludeAlreadyPayedAtDate ? CreditCalculator::calculateNextPaymentDate() : null;
    }

    /**
     * @param CreditCardConsume $consume
     * @return bool
     * @throws Exception
     */
    private function isRecentlyPayed(CreditCardConsume $consume): bool
    {
        $dates = array_map([$this, 'extractMonthsOfPayments'], $consume->getPayments()->toArray());

        $lastPaymentDate = DateHelper::calculateMajorMonth($dates);
        return 1 >= DateHelper::calculateDatesDifferenceMonths($lastPaymentDate,
            CreditCalculator::calculateNextPaymentDate());
    }

    private function extractMonthsOfPayments(CreditCardPayment $payment): string
    {
        return $payment->getMonthPayed() ?? $payment->getCreatedAt()->format('Y-m');
    }

}