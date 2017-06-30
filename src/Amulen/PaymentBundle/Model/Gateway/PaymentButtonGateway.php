<?php

namespace Amulen\PaymentBundle\Model\Gateway;

use Amulen\PaymentBundle\Model\Exception\GatewayException;
use Amulen\PaymentBundle\Model\Payment\PaymentInfo;


/**
 * Interface PaymentGateway
 * @package Amulen\PaymentBundle\Model
 */
interface PaymentButtonGateway
{

    /**
     * Return the payment link.
     *
     * @param PaymentInfo $paymentInfo
     * @return string
     * @throws GatewayException
     */
    public function getLinkUrl($paymentInfo);

    /**
     * Validate the Payment.
     *
     * @param PaymentInfo $paymentInfo
     * @return Response
     * @throws GatewayException
     */
    public function validatePayment($paymentInfo);

}