<?php


namespace App\Service\CreditCard;


use App\Entity\CreditCard\CreditCardConsume;
use App\Entity\CreditCard\CreditCardUser;
use App\Extractor\CreditCard\CreditCardConsumeExtractor;
use Exception;
use http\Exception\InvalidArgumentException;

class ConsumeResolver
{
    /**
     * @var CreditCardConsumeProvider
     */
    private $consumeProvider;
    /**
     * @var CreditCardConsumeExtractor
     */
    private $consumeExtractor;

    public function __construct(
        CreditCardConsumeProvider $consumeProvider,
        CreditCardConsumeExtractor $consumeExtractor
    ) {
        $this->consumeProvider = $consumeProvider;
        $this->consumeExtractor = $consumeExtractor;
    }

    /**
     * @param CreditCardConsume[] $consumes
     * @return float
     * @throws Exception
     */
    public function resolveTotalDebtOfConsumesArray(array $consumes): float
    {
        $totalPayment = 0;
        foreach ($consumes as $consume) {

            if (!$consume instanceof CreditCardConsume) {
                throw new InvalidArgumentException('El array espera recibir objetos del tipo ' . CreditCardConsume::class . ' se recibió ' . get_class($consume));
            }

            $actualDebt = $this->consumeExtractor->extractActualDebt($consume);
            $interest = $this->consumeExtractor->extractNextInterestAmount($consume);

            $totalPayment += ($actualDebt + $interest);
        }

        return $totalPayment;
    }

    /**
     * @param CreditCardConsume[] $consumes
     * @return float|int
     * @throws Exception
     */
    public function resolveTotalInterestToPayByConsumesArray(array $consumes): float
    {
        $interestToPay = 0;
        foreach ($consumes as $consume) {
            $interestToPay += $this->consumeExtractor->extractNextInterestAmount($consume);
        }

        return $interestToPay;
    }
}