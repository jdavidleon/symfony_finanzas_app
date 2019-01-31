<?php
/**
 * Created by PhpStorm.
 * User: jleon
 * Date: 24/01/2019
 * Time: 4:51 PM
 */

namespace App\Service\Personal;

use App\Entity\Personal\PersonalBalance;
use App\Entity\Security\User;
use App\Repository\Personal\EntryRepository;
use Doctrine\ORM\EntityManagerInterface;

class BalanceCalculations
{

    /**
     * @var EntryRepository
     */
    private $personalBalanceRepository;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    public function __construct(
        EntryRepository $personalBalanceRepository,
        EntityManagerInterface $entityManager
    )
    {
        $this->personalBalanceRepository = $personalBalanceRepository;
        $this->entityManager = $entityManager;
    }

    /**
     * @param User $user
     * @param array $balanceSetters
     * @throws \Exception
     */
    public function handleBalance(User $user, array $balanceSetters)
    {
        $balance = $this->getThisMonthBalance($user);
        if ( $balance ){
            $this->updateBalanceMonth($balance, $balanceSetters);
        }else{
            $this->setNewBalanceMonth($user,$balanceSetters);
        }
    }

    /**
     * @param User $user
     * @param array $balanceSetters
     * @throws \Exception
     */
    public function setNewBalanceMonth(User $user, array $balanceSetters=[])
    {
        $lastMonthBalance = $this->getLastMonthBalance($user);

        $entries = $balanceSetters['entry'] ?? 0 + $lastMonthBalance->getEndMoney() ?? 0;
        $egresses = $balanceSetters['egress'] ?? 0;

        $balance = new PersonalBalance();
        $balance->setEntries( $entries );
        $balance->setEgresses( $egresses );
        $balance->setEndMoney( $entries - $egresses );
        $balance->setUser($user);
        $balance->setMonth($this->getActualMonth());

        $this->entityManager->persist($balance);
        $this->entityManager->flush();
    }

    private function updateBalanceMonth(PersonalBalance $balance, array $balanceSetters)
    {
        $entries = $balance->getEntries() + $balanceSetters['entry'] ?? 0;
        $egresses = $balance->getEgresses() + $balanceSetters['egress'] ?? 0;
        $endMoney = $balance->getEndMoney() + $entries - $egresses;

        $balance->setEntries($entries);
        $balance->setEgresses($egresses);
        $balance->setEndMoney($endMoney);

        $this->entityManager->persist($balance);
        $this->entityManager->flush();
    }

    protected function getLastMonth(): string
    {
        $year = $month = '';
        if (\date('m') > 1) {
            $month = \date('m') - 1;
            $year = \date('Y');
        } elseif (\date('m') == 1) {
            $month = 12;
            $year = \date('y') - 1;
        }
        return $year . '-' . $month . '-1';
    }

    /**
     * @param User $user
     * @return PersonalBalance|object|null
     */
    private function getLastMonthBalance(User $user)
    {
        return $this->personalBalanceRepository->findOneBy([
            'user' => $user,
            'month' => $this->getLastMonth()
        ]);
    }

    /**
     * @param User $user
     * @return PersonalBalance|object|null
     */
    private function getThisMonthBalance(User $user)
    {
        return $this->personalBalanceRepository->findOneBy([
            'user' => $user,
            'month' => $this->getActualMonth()
        ]);
    }

    /**
     * @return false|string
     */
    private function getActualMonth()
    {
        return date('Y-m');
    }

}