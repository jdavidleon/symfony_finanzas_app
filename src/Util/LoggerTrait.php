<?php


namespace App\Util;


use Psr\Log\LoggerInterface;

trait LoggerTrait
{
    /**
     * @var LoggerInterface|null
     * */
    private $logger;

    /**
     * @param LoggerInterface $logger
     * @required
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function addLog($level, $message, $context)
    {
        if ($this->logger) {
            $this->logger->log($level, $message, $context);
        }
    }
}