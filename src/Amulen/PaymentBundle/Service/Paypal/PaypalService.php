<?php

namespace Amulen\PaymentBundle\Service\Paypal;

use Doctrine\ORM\EntityManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

class PaypalService implements PaypalServiceInterface {

    protected $container;
    protected $em;

    public function __construct(EntityManager $em, ContainerInterface $container) {
        $this->em = $em;
        $this->container = $container;
    }

    public function verifyNotification($data) {
        return;
        }


}
