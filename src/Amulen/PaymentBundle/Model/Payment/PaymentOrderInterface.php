<?php
namespace Amulen\PaymentBundle\Model\Payment;

interface PaymentOrderInterface
{
    public function getId();
    public function getTotal();
}
