<?php


namespace App\EventListener;


use App\Entity\CreditCard\CreditCardConsume;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Exception;
use Psr\Log\LoggerInterface;

class ConsumeChangeNotifier
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    private $message = 'Monto total pago no corresponde al valor registrado en la lista de pagos';

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @param CreditCardConsume $cardConsume
     * @param LifecycleEventArgs $eventArgs
     * @throws Exception
     */
    public function postUpdate(CreditCardConsume $cardConsume, LifecycleEventArgs $eventArgs)
    {
        $amountInPayments = 0;
        foreach ($cardConsume->getPayments() as $payment) {
            $amountInPayments += $payment->getCapitalAmount();
        }

        if (round($cardConsume->getAmountPayed(), 0) != round($amountInPayments, 0)) {
            $this->logger->alert($this->message, [
                'consume' => $cardConsume->getId(),
                'amount_payed' => $cardConsume->getAmountPayed(),
                'amount_in_payments' => $amountInPayments,
                'payment_error' => $cardConsume->getPayments()->last()->getId()
            ]);

            throw new Exception($this->message . '. Debes contactar soporte!');
        }
    }
}