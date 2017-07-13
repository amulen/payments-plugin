<?php

namespace Amulen\PaymentBundle\Service\Paypal;

interface PaypalServiceInterface {

    function verifyNotification($data);
}
