<?php

namespace Amulen\PaymentBundle\Event;

use Symfony\Component\EventDispatcher\Event;

/**
 * ProcessedPaymentEvent
 */
class ProcessedPaymentEvent extends Event
{
    const NAME = 'amulen.payment.event.processed_payment';

    protected $paymentStatus;
    protected $orderId;
    protected $nextRouteName;

    /**
     * ProcessedPaymentEvent constructor.
     * @param $paymentStatus
     */
    public function __construct($paymentStatus)
    {
        $this->paymentStatus = $paymentStatus;
    }

    /**
     * @return mixed
     */
    public function getPaymentStatus()
    {
        return $this->paymentStatus;
    }

    /**
     * @param mixed $paymentStatus
     */
    public function setPaymentStatus($paymentStatus)
    {
        $this->paymentStatus = $paymentStatus;
    }


    /**
     * @return mixed
     */
    public function getOrderId()
    {
        return $this->orderId;
    }

    /**
     * @param mixed $orderId
     */
    public function setOrderId($orderId)
    {
        $this->orderId = $orderId;
    }

    /**
     * @return mixed
     */
    public function getNextRouteName()
    {
        return $this->nextRouteName;
    }

    /**
     * @param mixed $nextRouteName
     */
    public function setNextRouteName($nextRouteName)
    {
        $this->nextRouteName = $nextRouteName;
    }


}