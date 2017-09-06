<?php
namespace Amulen\PaymentBundle\Model\Gateway\Paypal;

class PaypalPlan
{

    private $name;
    private $description;
    private $payments;
    private $paypalId;

    public function getPaypalId()
    {
        return $this->paypalId;
    }

    public function setPaypalId($paypalId)
    {
        $this->paypalId = $paypalId;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function getPayments()
    {
        return $this->payments;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function setDescription($description)
    {
        $this->description = $description;
    }

    public function setPayments($payments)
    {
        $this->payments = $payments;
    }
}
