<?php
namespace Amulen\PaymentBundle\Service\Paypal;

use Amulen\PaymentBundle\Model\Gateway\Paypal\PaypalPlan;

interface PaypalServiceInterface
{

    function verifyNotification($data);

    function createPlan(PaypalPlan $paypalPlan);

}
