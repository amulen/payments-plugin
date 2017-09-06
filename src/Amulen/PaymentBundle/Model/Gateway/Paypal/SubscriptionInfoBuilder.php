<?php
namespace Amulen\PaymentBundle\Model\Gateway\Paypal;

use Amulen\PaymentBundle\Model\Payment\SubscriptionInfo;
use Amulen\PaymentBundle\Model\Payment\SubscriptionInfoItem;
use Symfony\Component\HttpFoundation\Request;
use Amulen\PaymentBundle\Model\Gateway\SubscriptionInfoBuilderInterface;
use Amulen\PaymentBundle\Model\Payment\SubscriptionOrderInterface;
use Flowcode\UserBundle\Entity\UserInterface;

class SubscriptionInfoBuilder implements SubscriptionInfoBuilderInterface
{

    public function buildForButtonGateway(SubscriptionOrderInterface $order, UserInterface $user, $method = null)
    {
        $subscriptionInfo = new SubscriptionInfo();
        $subscriptionInfo->setName($order->getName());
        $subscriptionInfo->setDescription($order->getDescription());
        $subscriptionInfo->setPlanId($order->getPlanId());
        $subscriptionInfo->setReturnUrl($order->getReturnUrl());
        $subscriptionInfo->setCancelUrl($order->getCancelUrl());
        $subscriptionInfo->setNotifyUrl($order->getNotifyUrl());
        return $subscriptionInfo;
    }
}
