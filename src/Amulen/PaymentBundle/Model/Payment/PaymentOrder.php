<?php

namespace Amulen\PaymentBundle\Model\Payment;

/**
 * @author juliansci
 */
class PaymentOrder implements PaymentOrderInterface {

    private $id;
    private $total;
    private $description;
    private $brandName;
    private $brandLogo;
    private $paymentId;
    private $payerId;

    function getId() {
        return $this->id;
    }

    function getTotal() {
        return $this->total;
    }

    function setId($id) {
        $this->id = $id;
    }

    function setTotal($total) {
        $this->total = $total;
    }

    function getDescription() {
        return $this->description;
    }

    function setDescription($description) {
        $this->description = $description;
    }

    function getBrandName() {
        return $this->brandName;
    }

    function getBrandLogo() {
        return $this->brandLogo;
    }

    function setBrandName($brandName) {
        $this->brandName = $brandName;
    }

    function setBrandLogo($brandLogo) {
        $this->brandLogo = $brandLogo;
    }

    function getPaymentId() {
        return $this->paymentId;
    }

    function getPayerId() {
        return $this->payerId;
    }

    function setPaymentId($paymentId) {
        $this->paymentId = $paymentId;
    }

    function setPayerId($payerId) {
        $this->payerId = $payerId;
    }

}
