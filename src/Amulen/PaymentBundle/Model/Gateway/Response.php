<?php

namespace Amulen\PaymentBundle\Model\Gateway;


use Amulen\PaymentBundle\Model\Payment\Status;

/**
 * Gateway response.
 */
class Response
{
    private $status;
    private $message;
    private $orderId;

    /**
     * Response constructor.
     */
    public function __construct()
    {
        $this->status = Status::REJECTED;
    }


    /**
     * @return mixed
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param mixed $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     * @return mixed
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @param mixed $message
     */
    public function setMessage($message)
    {
        $this->message = $message;
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

}