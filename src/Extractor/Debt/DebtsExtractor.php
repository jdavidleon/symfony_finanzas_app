<?php
/**
 * Created by PhpStorm.
 * User: IT OPTIME
 * Date: 29/01/2019
 * Time: 11:11 AM
 */

namespace App\Extractor\Debt;


use App\Entity\Debts\Credits;
use App\Entity\Debts\Debt;
use App\Entity\Debts\FixedCharges;
use App\Entity\Security\User;
use App\Repository\Debts\CreditRepository;
use App\Repository\Debts\DebtRepository;
use App\Repository\Debts\FixedChargesRepository;

class DebtsExtractor
{
    /**
     * @var FixedChargesRepository
     */
    private $creditRepository;
    /**
     * @var FixedChargesRepository
     */
    private $fixedChargesRepository;
    /**
     * @var DebtRepository
     */
    private $debtRepository;

    public function __construct(
        CreditRepository $creditRepository,
        FixedChargesRepository $fixedChargesRepository,
        DebtRepository $debtRepository
    )
    {
        $this->creditRepository = $creditRepository;
        $this->fixedChargesRepository = $fixedChargesRepository;
        $this->debtRepository = $debtRepository;
    }

    /**
     * @param User $user
     * @return array
     */
    public function getNextDebtsByUser(User $user): ?array
    {
        $nextDebts = [];
        $credits = $this->getActiveCreditsByUser($user);

        /** @var Credits $credit */
        foreach ($credits as $credit){
            $nextDebts[] = [
                'type' => 'credit',
                'id' => $credit->getId(),
                'pay_day' => $credit->getPaymentDay()
            ];
        }

        $fixedCharges = $this->getActiveFixedChargesByUser($user);

        /** @var FixedCharges $fixed */
        foreach ($fixedCharges as $fixed){
            $nextDebts[] = [
                'type' => 'credit',
                'id' => $fixed->getId(),
                'pay_day' => $fixed->getPayDay()
            ];
        }

        $debts = $this->getActiveDebtsByUser($user);

        /** @var Debt $debt */
        foreach ($debts as $debt){
            $nextDebts[] = [
                'type' => 'debt',
                'id' => $debt->getId(),
                'pay_day' => $debt->getPaymentDay()
            ];
        }

        return $nextDebts;
    }

    /**
     * @param User $user
     * @throws \Exception
     */
    public function getNextCreditsByUserAndComingDays(User $user)
    {
        $this->creditRepository->getNextCreditsByUserByComingDays($user, 20);
    }

    public function getActiveCreditsByUser(User $user)
    {
        return $this->creditRepository->getActualCreditsByUser($user);
    }

    public function getActiveFixedChargesByUser(User $user)
    {
        return $this->fixedChargesRepository->getActualFixedChargesByUser($user);
    }

    public function getActiveDebtsByUser(User $user)
    {
        return $this->debtRepository->getActualDebtsByUser($user);
    }
}