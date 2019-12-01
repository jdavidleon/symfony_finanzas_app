<?php


namespace App\Exception;


class MinimalAmountPaymentRequiredException extends \LogicException
{
    protected $message = "The Amount you ty to pay is minor to Actual Payment Due";
}