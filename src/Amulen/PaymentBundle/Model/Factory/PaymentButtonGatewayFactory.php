<?php

namespace Amulen\PaymentBundle\Model\Factory;

use Amulen\PaymentBundle\Model\Gateway\Mp\Setting;
use Amulen\PaymentBundle\Model\Gateway\PaymentButtonGateway;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Created by PhpStorm.
 * User: pela
 * Date: 6/1/17
 * Time: 3:31 PM
 */
class PaymentButtonGatewayFactory
{

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * PaymentButtonGatewayFactory constructor.
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @param $gatewayId
     * @return PaymentButtonGateway
     */
    public function getPaymentButtonGateway($gatewayId)
    {
        switch ($gatewayId) {

            case Setting::GATEWAY_ID:
                return $this->container->get('amulen_payment.gateway.button.mp');
                break;

            default:
                return null;
        }
    }
}