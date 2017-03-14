<?php

namespace Amulen\PaymentBundle\Controller\Nps;

use Amulen\NpsBundle\Service\PaymentService;
use Amulen\PaymentBundle\Event\ProcessedPaymentEvent;
use Amulen\PaymentBundle\Model\Gateway\PaymentButtonGateway;
use Amulen\PaymentBundle\Model\Payment\PaymentInfo;
use Amulen\PaymentBundle\Model\Payment\Status;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;

class ButtonController extends Controller
{

    /**
     * @Template()
     */
    public function defaultAction($amount, $customerMail)
    {

        /* @var PaymentButtonGateway $paymentGateway */
        $paymentGateway = $this->get('amulen_payment.gateway.button.nps');

        $paymentInfo = new PaymentInfo();
        $paymentInfo->setUnitPrice($amount);
        $paymentInfo->setCustomerMail($customerMail);

        $url = $paymentGateway->getLinkUrl($paymentInfo);

        return [
            'link' => $url
        ];
    }
}
