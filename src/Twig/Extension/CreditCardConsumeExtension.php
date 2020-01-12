<?php


namespace App\Twig\Extension;

use App\Entity\CreditCard\CreditCard;
use App\Entity\CreditCard\CreditCardConsume;
use App\Entity\CreditCard\CreditCardUser;
use App\Entity\Security\User;
use App\Extractor\CreditCard\CreditCardConsumeExtractor;
use Exception;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class CreditCardConsumeExtension extends AbstractExtension
{

    /**
     * @var CreditCardConsumeExtractor
     */
    private $consumeExtractor;

    public function __construct(
        CreditCardConsumeExtractor $consumeExtractor
    ) {
        $this->consumeExtractor = $consumeExtractor;
    }

    public function getFunctions()
    {
        return [
          new TwigFunction('actualDebtByConsume', [$this, 'actualDebtByConsume']),
          new TwigFunction('nextPaymentAmountByConsume', [$this, 'nextPaymentAmountByConsume']),
          new TwigFunction('nextCapitalAmountByConsume', [$this, 'nextCapitalAmountByConsume']),
          new TwigFunction('nextInteresAmountByConsume', [$this, 'nextInteresAmountByConsume']),
          new TwigFunction('nextPaymentMonthByConsume', [$this, 'nextPaymentMonthByConsume']),
          new TwigFunction('nextPaymentAmo', [$this, 'nextPaymentMonthByConsume']),
          new TwigFunction('totalByCreditCard', [$this, 'totalByCreditCard']),
          new TwigFunction('totalByCardUser', [$this, 'totalByCardUser']),
          new TwigFunction('totalByOwner', [$this, 'totalByOwner']),
          new TwigFunction('nextPaymentMonth', [$this, 'nextPaymentMonth']),
          new TwigFunction('actualDueToPayByConsume', [$this, 'actualDueToPayByConsume']),
        ];
    }

    /**
     * @param CreditCardConsume $cardConsume
     * @return float|int|null
     * @throws Exception
     */
    public function actualDebtByConsume(CreditCardConsume $cardConsume)
    {
        return $this->consumeExtractor->extractActualDebt($cardConsume);
    }

    /**
     * @param CreditCardConsume $cardConsume
     * @return float|int|null
     * @throws Exception
     */
    public function nextPaymentAmountByConsume(CreditCardConsume $cardConsume)
    {
        return $this->consumeExtractor->extractNextPaymentAmount($cardConsume);
    }

    /**
     * @param CreditCardConsume $cardConsume
     * @return float|int|null
     * @throws Exception
     */
    public function nextCapitalAmountByConsume(CreditCardConsume $cardConsume)
    {
        return $this->consumeExtractor->extractNextCapitalAmount($cardConsume);
    }

    /**
     * @param CreditCardConsume $cardConsume
     * @return float|int|null
     * @throws Exception
     */
    public function nextInteresAmountByConsume(CreditCardConsume $cardConsume)
    {
        return $this->consumeExtractor->extractNextInterestAmount($cardConsume);
    }

    /**
     * @param CreditCardConsume $cardConsume
     * @return float|int|null
     * @throws Exception
     */
    public function nextPaymentMonthByConsume(CreditCardConsume $cardConsume)
    {
        return $this->consumeExtractor->extractNextPaymentAmount($cardConsume);
    }

    /**
     * @param CreditCard $card
     * @param null $month
     * @return float
     * @throws Exception
     */
    public function totalByCreditCard(CreditCard $card, $month = null)
    {
        return $this->consumeExtractor->extractTotalToPayByCreditCard($card, $month);
    }

    /**
     * @param CreditCardUser $cardUser
     * @param CreditCard|null $card
     * @param bool $month
     * @return float
     * @throws Exception
     */
    public function totalByCardUser(CreditCardUser $cardUser, CreditCard $card = null, bool $month = false)
    {
        return $this->consumeExtractor->extractTotalToPayByCardUser($cardUser, $card, $month);
    }

    /**
     * @param User $user
     * @return float|int|null
     * @throws Exception
     */
    public function totalByOwner(User $user)
    {
        return $this->consumeExtractor->extractTotalToPayByOwner($user);
    }

    /**
     * @return string
     * @throws Exception
     */
    public function nextPaymentMonth()
    {
        return $this->consumeExtractor->extractNextPaymentMonth();
    }

    /**
     * @param CreditCardConsume $cardConsume
     * @return int
     * @throws Exception
     */
    public function actualDueToPayByConsume(CreditCardConsume $cardConsume)
    {
        return $this->consumeExtractor->extractActualDueToPay($cardConsume);
    }
}