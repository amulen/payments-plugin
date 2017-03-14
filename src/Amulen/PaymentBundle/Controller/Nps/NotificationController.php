<?php

namespace Amulen\PaymentBundle\Controller\Nps;

use Amulen\NpsBundle\Service\PaymentService;
use Amulen\PaymentBundle\Event\ProcessedPaymentEvent;
use Amulen\PaymentBundle\Model\Gateway\PaymentButtonGateway;
use Amulen\PaymentBundle\Model\Payment\PaymentInfo;
use Amulen\PaymentBundle\Model\Payment\Status;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;

class NotificationController extends Controller
{
    /**
     * @Route("/amulen_payment/notification/nps", name="amulen_payment_notification_nps")
     * @Method("POST")
     */
    public function receiveAction(Request $request)
    {

        /* @var PaymentButtonGateway $paymentGateway */
        $paymentGateway = $this->get('amulen_payment.gateway.button.nps');

        $paymentInfo = new PaymentInfo();
        $paymentInfo->setTransactionId($request->get('psp_TransactionId'));

        $session = $request->getSession();

        $npsResponse = $paymentGateway->validatePayment($paymentInfo);

        if ($npsResponse->getStatus() == Status::APPROVED) {
            $session->remove('productOrderId');
        }

        $processedPaymentEvent = new ProcessedPaymentEvent($npsResponse->getStatus());

        $dispatcher = $this->get('event_dispatcher');
        $dispatcher->dispatch(ProcessedPaymentEvent::NAME, $processedPaymentEvent);

        $session->set('error_message', $npsResponse->getMessage());

        return $this->redirectToRoute($processedPaymentEvent->getNextRouteName());
    }
}
