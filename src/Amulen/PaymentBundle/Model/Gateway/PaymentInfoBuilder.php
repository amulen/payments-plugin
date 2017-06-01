<?php


namespace Amulen\PaymentBundle\Model\Gateway;


use Amulen\PaymentBundle\Model\Payment\PaymentInfo;
use Symfony\Component\HttpFoundation\Request;

interface PaymentInfoBuilder
{

    /**
     * @param Request $request
     * @return PaymentInfo
     */
    public function buildFromRequest(Request $request);

}