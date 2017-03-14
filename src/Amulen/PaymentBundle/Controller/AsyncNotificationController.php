<?php

namespace Amulen\PaymentBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class AsyncNotificationController extends Controller
{
    /**
     * @Route("/amulen_payment/async_notification/{gatewayId}", name="amulen_nps_payment_async_notification")
     * @Method("POST")
     * @Template()
     */
    public function receiveAction(Request $request)
    {

        /* @var PaymentService $npsPaymentService */
        $npsPaymentService = $this->get('nps.payment.service');

        $params = [
            'transaction_id' => $request->get('psp_TransactionId'),
        ];

        $session = $request->getSession();
        $npsResponse = $npsPaymentService->getTxStatus($params);

        if ($npsResponse->getStatus() == \Amulen\NpsBundle\Model\Response::API_OK) {

            /* @var ProductOrderService $productOrderService */
            $productOrderService = $this->get('amulen.shop.order');

            $productOrder = $productOrderService->getProductOrder($npsResponse->setOrderId());
            $productOrderService->changeStatusTo($productOrder, ProductOrderStatus::STATUS_PAYED);

            $session->remove('productOrderId');

            return $this->redirectToRoute('product_order_confirmed');
        }

        $session->set('error_message', $npsResponse->getMessage());

        return $this->redirectToRoute('product_order_confirmed_error');
    }
}
