<?php
/**
 * Created by PhpStorm.
 * User: IT OPTIME
 * Date: 24/01/2019
 * Time: 4:51 PM
 */

namespace App\Service\Personal;


use App\Repository\Personal\PersonalBalanceRepository;

class BalanceCalculations
{

    /**
     * @var PersonalBalanceRepository
     */
    private $personalBalanceRepository;

    public function __construct(
        PersonalBalanceRepository $personalBalanceRepository
    )
    {
        $this->personalBalanceRepository = $personalBalanceRepository;
    }


    public function balanceSheet()
    {
        $lastMonth = $this->getLastMonth();

        if ($this->balanceExist($lastMonth)){

        }

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
     * @param string $lastMonth
     * @return bool
     */
    protected function balanceExist(string $lastMonth): bool
    {
        $balanceExist = $this->personalBalanceRepository->findBy([
            'month' => $lastMonth
        ]);

        return count($balanceExist) > 0;
    }

}