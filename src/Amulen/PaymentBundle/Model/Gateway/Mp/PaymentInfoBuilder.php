<?php
namespace Amulen\PaymentBundle\Model\Gateway\Mp;

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
        $paymentInfo = new PaymentInfo();
        $paymentInfo->setTransactionId($request->query->get('id'));
        $paymentInfo->setPaymentReference($request->query->get('topic'));

        return $paymentInfo;
    }
}