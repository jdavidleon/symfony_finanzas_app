<?php


namespace App\Exception;


class ExcedeAmountDebtException extends \LogicException
{
    protected $message = "The Amount you ty to pay is major to Actual debt";
}