<?php


namespace App\Exception;

/**
 * Esta excepción se lanza cuando el formato de Fecha esta incorrecto debe ser del tipo 'Y-m-d'
 * */
class InvalidDateTimeFormat extends \LogicException
{
    protected $message = 'Invalid Date Format, expected Y-m-d.';
}