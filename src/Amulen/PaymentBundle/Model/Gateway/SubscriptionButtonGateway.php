<?php
namespace Amulen\PaymentBundle\Model\Gateway;

use Amulen\PaymentBundle\Model\Payment\SubscriptionInfo;

interface SubscriptionButtonGateway
{

    public function getLinkUrl(SubscriptionInfo $subscriptionInfo);

    public function validateSubscription(SubscriptionInfo $subscriptionInfo);
}
