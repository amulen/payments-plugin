<?php

namespace Amulen\PaymentBundle\Model\Gateway\Nps;


use Amulen\PaymentBundle\Model\Payment\PaymentInfo;
use Symfony\Component\HttpFoundation\Request;

class PaymentInfoBuilder implements \Amulen\PaymentBundle\Model\Gateway\PaymentInfoBuilder
{
    /**
     * @param Request $request
     * @return PaymentInfo
     */
    public function buildFromRequest(Request $request)
    {
        // TODO: Implement buildFromRequest() method.
    }

    /**
     * @param $order
     * @param $user
     * @param null $method
     * @return PaymentInfo
     */
    public function buildForButtonGateway($order, $user, $method = null)
    {
        $currency = '032';

        $paymentInfo = new PaymentInfo();
        $paymentInfo->setUnitPrice(number_format($order->getTotal(), 2, '', ''));
        $paymentInfo->setCustomerMail($user->getEmail());
        $paymentInfo->setCurrencyId($currency);
        $paymentInfo->setMethodId($method);
        $paymentInfo->setOrderId($order->getId());

        return $paymentInfo;
    }
}