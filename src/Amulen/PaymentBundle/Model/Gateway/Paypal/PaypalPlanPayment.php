<?php
namespace Amulen\PaymentBundle\Model\Gateway\Paypal;

class PaypalPlanPayment
{

    private $name;
    private $frequencyInterval;
    private $cycles;
    private $amount;
    private $type;

    public function getType()
    {
        return $this->type;
    }

    public function setType($type)
    {
        $this->type = $type;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getFrequencyInterval()
    {
        return $this->frequencyInterval;
    }

    public function setFrequencyInterval($frequencyInterval)
    {
        $this->frequencyInterval = $frequencyInterval;
    }

    public function getCycles()
    {
        return $this->cycles;
    }

    public function getAmount()
    {
        return $this->amount;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function setCycles($cycles)
    {
        $this->cycles = $cycles;
    }

    public function setAmount($amount)
    {
        $this->amount = $amount;
    }
}
