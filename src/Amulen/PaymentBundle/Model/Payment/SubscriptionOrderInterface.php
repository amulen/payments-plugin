<?php
namespace Amulen\PaymentBundle\Model\Payment;

interface SubscriptionOrderInterface
{

    public function getName();

    public function getDescription();

    public function getPlanId();

    public function getReturnUrl();

    public function getCancelUrl();

}
