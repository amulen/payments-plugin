<?php
namespace Amulen\PaymentBundle\Model\Gateway;

use Amulen\PaymentBundle\Model\Payment\PaymentInfo;
use Symfony\Component\HttpFoundation\Request;
use Flowcode\UserBundle\Entity\UserInterface;
use Amulen\PaymentBundle\Model\Payment\SubscriptionOrderInterface;

interface SubscriptionInfoBuilderInterface
{

    /**
     * @return PaymentInfo
     */
    public function buildForButtonGateway(SubscriptionOrderInterface $order, UserInterface $user, $method = null);

}