<?php

namespace Amulen\PaymentBundle\Model\Payment;

/**
 * Payment Information.
 *
 */
class PaymentInfo
{

    private $unitPrice;
    private $quantity;
    private $orderId;
    private $transactionId;
    private $currencyId;
    private $methodId;
    private $customerMail;
    private $language;

    /**
     * @return mixed
     */
    public function getUnitPrice()
    {
        return $this->unitPrice;
    }

    /**
     * @param mixed $unitPrice
     */
    public function setUnitPrice($unitPrice)
    {
        $this->unitPrice = $unitPrice;
    }

    /**
     * @return mixed
     */
    public function getQuantity()
    {
        return $this->quantity;
    }

    /**
     * @param mixed $quantity
     */
    public function setQuantity($quantity)
    {
        $this->quantity = $quantity;
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
    public function getCurrencyId()
    {
        return $this->currencyId;
    }

    /**
     * @param mixed $currencyId
     */
    public function setCurrencyId($currencyId)
    {
        $this->currencyId = $currencyId;
    }

    /**
     * @return mixed
     */
    public function getMethodId()
    {
        return $this->methodId;
    }

    /**
     * @param mixed $methodId
     */
    public function setMethodId($methodId)
    {
        $this->methodId = $methodId;
    }

    /**
     * @return mixed
     */
    public function getCustomerMail()
    {
        return $this->customerMail;
    }

    /**
     * @param mixed $customerMail
     */
    public function setCustomerMail($customerMail)
    {
        $this->customerMail = $customerMail;
    }

    /**
     * @return mixed
     */
    public function getLanguage()
    {
        return $this->language;
    }

    /**
     * @param mixed $language
     */
    public function setLanguage($language)
    {
        $this->language = $language;
    }

    /**
     * @return mixed
     */
    public function getTransactionId()
    {
        return $this->transactionId;
    }

    /**
     * @param mixed $transactionId
     */
    public function setTransactionId($transactionId)
    {
        $this->transactionId = $transactionId;
    }


}