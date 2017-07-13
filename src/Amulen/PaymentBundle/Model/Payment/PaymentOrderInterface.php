<?php

namespace Amulen\PaymentBundle\Model\Payment;

interface PaymentOrderInterface {

    public function getId();

    public function getTotal();

    public function getDescription();

    public function getBrandName();

    public function getBrandLogo();

    public function getPaymentId();

    public function getPayerId();
}
