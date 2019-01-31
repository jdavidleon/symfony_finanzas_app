<?php
/**
 * Created by PhpStorm.
 * User: IT OPTIME
 * Date: 31/01/2019
 * Time: 10:55 AM
 */

namespace App\Extractor\Personal;


use App\Entity\Personal\PersonalBalance;
use App\Entity\Security\User;
use App\Repository\Personal\EntryRepository;
use App\Service\Personal\BalanceCalculations;

class PersonalExtractor
{

    /**
     * @var EntryRepository
     */
    private $personalBalanceRepository;
    /**
     * @var BalanceCalculations
     */
    private $balanceCalculations;
    /**
     * @var EntryRepository
     */
    private $entryRepository;

    public function __construct(
        EntryRepository $personalBalanceRepository,
        BalanceCalculations $balanceCalculations,
        EntryRepository $entryRepository
    )
    {
        $this->personalBalanceRepository = $personalBalanceRepository;
        $this->balanceCalculations = $balanceCalculations;
        $this->entryRepository = $entryRepository;
    }


    /**
     * @param User $user
     * @return PersonalBalance
     * @throws \Exception
     */
    public function obtainCurrentMoneyByUser(User $user)
    {
        $currentMoney = $this->getCurrentMoneyByUser($user);
        if ($currentMoney){
            return $currentMoney->getEndMoney();
        }

        $this->balanceCalculations->setNewBalanceMonth($user);
        return $this->getCurrentMoneyByUser($user)->getEndMoney();
    }

    /**
     * @param User $user
     * @return PersonalBalance|null
     */
    public function getCurrentMoneyByUser(User $user): ?object
    {
        return $this->personalBalanceRepository->findOneBy([
           'user' => $user,
           'month' => $this->getActualMonth()
        ]);
    }

    public function getIncomesByUser(User $user)
    {
        return $this->entryRepository->find($user->getId());
    }

    protected function getActualMonth(): string
    {
         return date('Y-m').'-1';
    }

}