<?php

namespace Amulen\PaymentBundle\Model\Factory;

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
            case \Amulen\PaymentBundle\Model\Gateway\Mp\Setting::GATEWAY_ID:
                return $this->container->get('amulen_payment.gateway.button.mp');
                break;

            case \Amulen\PaymentBundle\Model\Gateway\Nps\Setting::GATEWAY_ID:
                return $this->container->get('amulen_payment.gateway.button.nps');
                break;
            
            case \Amulen\PaymentBundle\Model\Gateway\Paypal\Setting::GATEWAY_ID:
                return $this->container->get('amulen_payment.gateway.button.paypal');
                break;

            default:
                return null;
        }
    }
}