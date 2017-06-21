<?php
namespace Amulen\PaymentBundle\Model\Gateway;

use Amulen\PaymentBundle\Model\Payment\PaymentInfo;
use Symfony\Component\HttpFoundation\Request;
use Flowcode\UserBundle\Entity\UserInterface;
use Amulen\PaymentBundle\Model\Payment\PaymentOrderInterface;

interface PaymentInfoBuilderInterface
{

    /**
     * @param Request $request
     * @return PaymentInfo
     */
    public function buildFromRequest(Request $request);

    /**
     * @return PaymentInfo
     */
    public function buildForButtonGateway(PaymentOrderInterface $order, UserInterface $user, $method = null);

}