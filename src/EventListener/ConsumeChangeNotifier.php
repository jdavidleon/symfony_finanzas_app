<?php


namespace App\EventListener;


use App\Entity\CreditCard\CreditCardConsume;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Exception;
use Psr\Log\LoggerInterface;
use Swift_Mailer;

class ConsumeChangeNotifier
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    private $message = 'Monto total pago no corresponde al valor registrado en la lista de pagos';
    /**
     * @var Swift_Mailer
     */
    private $mailer;

    public function __construct(LoggerInterface $logger, Swift_Mailer $mailer)
    {
        $this->logger = $logger;
        $this->mailer = $mailer;
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

            $this->sendEmailNotification($cardConsume, $amountInPayments);

            throw new Exception($this->message . '. Debes contactar soporte!');
        }
    }

    public function sendEmailNotification(CreditCardConsume $cardConsume, $amountInPayments)
    {
        $message = new \Swift_Message('Alerta de Pagos de consumo');
        $message->setFrom('admin@sfa.com')
            ->setTo('jlp25@hotmail.com')
            ->setBody(
                sprintf('Ha ocurrido una inconsistencia con el pago realizado (%s), Monto pago $(%s), monto reportado en lista de pagos $(%s), Payment Error %s',
                    $cardConsume->getId(),
                    $cardConsume->getAmountPayed(),
                    $amountInPayments,
                    $cardConsume->getPayments()->last()->getId()
                )
            );

        $this->mailer->send($message);
    }
}