<?php
namespace Amulen\PaymentBundle\Model\Payment;

/**
 * @author juliansci
 */
class PaymentOrder implements PaymentOrderInterface
{

    private $id;
    private $total;

    function getId()
    {
        return $this->id;
    }

    function getTotal()
    {
        return $this->total;
    }

    function setId($id)
    {
        $this->id = $id;
    }

    function setTotal($total)
    {
        $this->total = $total;
    }
}
