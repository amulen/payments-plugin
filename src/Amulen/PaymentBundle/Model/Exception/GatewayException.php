<?php

namespace Amulen\PaymentBundle\Model\Exception;

/**
 * Gateway Exception.
 */
class GatewayException extends \Exception
{
    function __construct($msg)
    {
        parent::__construct($msg);
    }
}