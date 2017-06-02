<?php

namespace Amulen\PaymentBundle\Controller;

use Amulen\PaymentBundle\Model\Gateway\PaymentButtonGateway;
use Amulen\PaymentBundle\Model\Gateway\PaymentInfoBuilder;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class AsyncNotificationController extends Controller
{
    /**
     * @Route("/amulen_payment/async_notification/{gatewayId}", name="amulen_payment_async_notification")
     * @Method({"POST", "GET"})
     * @Template()
     */
    public function receiveAction(Request $request, $gatewayId)
    {

        $paymentInfoBuilderFactory = $this->get('amulen_payment.payment.info.builder.factory');
        $paymentButtonGatewayFactory = $this->get('amulen_payment.payment.button.gateway.factory');

        /* @var PaymentInfoBuilder $paymentInfoBuilder */
        $paymentInfoBuilder = $paymentInfoBuilderFactory->getPaymentInfoBuilder($gatewayId);
        $paymentInfo = $paymentInfoBuilder->buildFromRequest($request);

        /* @var PaymentButtonGateway $paymentButtonGateway */
        $paymentButtonGateway = $paymentButtonGatewayFactory->getPaymentButtonGateway($gatewayId);

        $response = new Response();
        $response->setStatusCode(Response::HTTP_BAD_REQUEST);

        if ($paymentButtonGateway->validatePayment($paymentInfo)) {
            $response->setStatusCode(Response::HTTP_OK);
        }

        return $response->send();
    }
}
