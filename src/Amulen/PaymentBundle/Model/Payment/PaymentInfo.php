<?php
namespace Amulen\PaymentBundle\Model\Payment;

use Doctrine\Common\Collections\ArrayCollection;

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
    private $customerId;
    private $methodId;
    private $paymentReference;
    private $customerMail;
    private $language;
    private $paymentInfoItems;
    private $description;
    private $brandName;
    private $brandLogo;
    private $paymentId;
    private $payerId;
    private $countryCode;
    private $returnUrl;
    private $cancelUrl;

    public function __construct()
    {
        $this->paymentInfoItems = new ArrayCollection();
    }

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
    public function getPaymentReference()
    {
        return $this->paymentReference;
    }

    /**
     * @param mixed $paymentReference
     */
    public function setPaymentReference($paymentReference)
    {
        $this->paymentReference = $paymentReference;
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

    /**
     * @return mixed
     */
    public function addPaymentInfoItem(PaymentInfoItem $paymentInfoItem)
    {
        $this->paymentInfoItems->add($paymentInfoItem);
    }

    /**
     * @return mixed
     */
    public function removePaymentInfoItem(PaymentInfoItem $paymentInfoItem)
    {
        $this->paymentInfoItems->removeElement($paymentInfoItem);
    }

    /**
     * @return PaymentInfoItem $paymentInfoItems
     */
    public function getPaymentInfoItems()
    {
        return $this->paymentInfoItems;
    }

    function getDescription()
    {
        return $this->description;
    }

    function setDescription($description)
    {
        $this->description = $description;
    }

    function getBrandName()
    {
        return $this->brandName;
    }

    function getBrandLogo()
    {
        return $this->brandLogo;
    }

    function setBrandName($brandName)
    {
        $this->brandName = $brandName;
    }

    function setBrandLogo($brandLogo)
    {
        $this->brandLogo = $brandLogo;
    }

    function getCustomerId()
    {
        return $this->customerId;
    }

    function setCustomerId($customerId)
    {
        $this->customerId = $customerId;
    }

    function getPaymentId()
    {
        return $this->paymentId;
    }

    function getPayerId()
    {
        return $this->payerId;
    }

    function setPaymentId($paymentId)
    {
        $this->paymentId = $paymentId;
    }

    function setPayerId($payerId)
    {
        $this->payerId = $payerId;
    }

    function getCountryCode()
    {
        return $this->countryCode;
    }

    function setCountryCode($countryCode)
    {
        $this->countryCode = $countryCode;
    }

    public function getReturnUrl()
    {
        return $this->returnUrl;
    }

    public function getCancelUrl()
    {
        return $this->cancelUrl;
    }

    public function setReturnUrl($returnUrl)
    {
        $this->returnUrl = $returnUrl;
    }

    public function setCancelUrl($cancelUrl)
    {
        $this->cancelUrl = $cancelUrl;
    }
}
