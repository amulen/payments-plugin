<?php

namespace Amulen\PaymentBundle\Model\Factory;

use Amulen\PaymentBundle\Model\Gateway\Mp\Setting;
use Amulen\PaymentBundle\Model\Gateway\PaymentInfoBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Created by PhpStorm.
 * User: pela
 * Date: 6/1/17
 * Time: 3:31 PM
 */
class PaymentInfoBuilderFactory
{

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * PaymentInfoBuilderFactory constructor.
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @param $gatewayId
     * @return PaymentInfoBuilder
     */
    public function getPaymentInfoBuilder($gatewayId)
    {
        switch ($gatewayId) {

            case \Amulen\PaymentBundle\Model\Gateway\Mp\Setting::GATEWAY_ID:
                return $this->container->get('amulen_payment.builder.mp');
                break;

            case \Amulen\PaymentBundle\Model\Gateway\Nps\Setting::GATEWAY_ID:
                return $this->container->get('amulen_payment.builder.nps');
                break;

            default:
                return null;
        }
    }
}