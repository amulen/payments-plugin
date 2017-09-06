<?php
namespace Amulen\PaymentBundle\Model\Payment;

/**
 * @author juliansci
 */
class SubscriptionOrder implements SubscriptionOrderInterface
{

    private $name;
    private $description;
    private $planId;
    private $returnUrl;
    private $cancelUrl;
    private $notifyUrl;

    public function getName()
    {
        return $this->name;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function setDescription($description)
    {
        $this->description = $description;
    }

    public function getPlanId()
    {
        return $this->planId;
    }

    public function setPlanId($planId)
    {
        $this->planId = $planId;
    }

    public function getReturnUrl()
    {
        return $this->returnUrl;
    }

    public function getCancelUrl()
    {
        return $this->cancelUrl;
    }

    public function getNotifyUrl()
    {
        return $this->notifyUrl;
    }

    public function setReturnUrl($returnUrl)
    {
        $this->returnUrl = $returnUrl;
    }

    public function setCancelUrl($cancelUrl)
    {
        $this->cancelUrl = $cancelUrl;
    }

    public function setNotifyUrl($notifyUrl)
    {
        $this->notifyUrl = $notifyUrl;
    }
}
