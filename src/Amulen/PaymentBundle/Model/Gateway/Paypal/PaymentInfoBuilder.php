<?php

namespace Amulen\PaymentBundle\Model\Gateway\Paypal;

use Amulen\PaymentBundle\Model\Payment\PaymentInfo;
use Amulen\PaymentBundle\Model\Payment\PaymentInfoItem;
use Symfony\Component\HttpFoundation\Request;
use Amulen\PaymentBundle\Model\Gateway\PaymentInfoBuilderInterface;
use Amulen\PaymentBundle\Model\Payment\PaymentOrderInterface;
use Flowcode\UserBundle\Entity\UserInterface;

class PaymentInfoBuilder implements PaymentInfoBuilderInterface
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

    /**
     * @param $order
     * @param $user
     * @param null $method
     * @return PaymentInfo
     */
    public function buildForButtonGateway(PaymentOrderInterface $order, UserInterface $user, $method = null)
    {
        $paymentInfo = new PaymentInfo();
        $paymentInfo->setUnitPrice($order->getTotal());
        $paymentInfo->setCustomerMail($user->getEmail());
        $paymentInfo->setDescription($order->getDescription());
        $paymentInfo->setCurrencyId('USD');
        $paymentInfo->setCustomerId($user->getId());
        $paymentInfo->setBrandName($order->getBrandName());
        $paymentInfo->setBrandLogo($order->getBrandLogo());
        $paymentInfo->setOrderId($order->getId());
        $paymentInfo->setPaymentId($order->getPaymentId());
        $paymentInfo->setPayerId($order->getPayerId());
        $paymentInfo->setCountryCode($order->getCountryCode());
        $paymentInfoItem = new PaymentInfoItem();
        $paymentInfoItem->setCurrencyId('USD');
        $paymentInfoItem->setQuantity(1);
        $paymentInfoItem->setUnitPrice($order->getTotal());
        $paymentInfoItem->setDescription($order->getDescription());
        $paymentInfo->addPaymentInfoItem($paymentInfoItem);
        return $paymentInfo;
    }
}
