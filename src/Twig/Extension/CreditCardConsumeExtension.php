<?php


namespace App\Twig\Extension;

use App\Entity\CreditCard\CreditCard;
use App\Entity\CreditCard\CreditCardUser;
use App\Entity\Security\User;
use App\Extractor\CreditCard\CreditCardConsumeExtractor;
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
    )
    {
        $this->consumeExtractor = $consumeExtractor;
    }

    public function getFunctions()
    {
        return [
          new TwigFunction('totalByCreditCard', [$this, 'totalByCreditCard']),
          new TwigFunction('totalByCardUser', [$this, 'totalByCardUser']),
          new TwigFunction('totalByOwner', [$this, 'totalByOwner']),
          new TwigFunction('nextPaymentMonth', [$this, 'nextPaymentMonth']),
        ];
    }

    public function totalByCreditCard(CreditCard $card, $month = null)
    {
        return $this->consumeExtractor->extractTotalToPayByCreditCard( $card, $month );
    }

    public function totalByCardUser(CreditCardUser $cardUser, CreditCard $card = null, $month = null)
    {
        return $this->consumeExtractor->extractTotalToPayByCardUser($cardUser, $card, $month);
    }

    public function totalByOwner(User $user)
    {
        return $this->consumeExtractor->extractTotalToPayByOwner($user);
    }

    public function nextPaymentMonth()
    {
        return $this->consumeExtractor->extractNextPaymentMonth();
    }
}