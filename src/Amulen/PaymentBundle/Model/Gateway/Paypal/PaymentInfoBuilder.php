<?php
namespace Amulen\PaymentBundle\Model\Gateway\Paypal;

use Amulen\PaymentBundle\Model\Payment\PaymentInfo;
use Amulen\PaymentBundle\Model\Payment\PaymentInfoItem;
use Symfony\Component\HttpFoundation\Request;
use Amulen\PaymentBundle\Model\Gateway\PaymentInfoBuilderInterface; 
use Amulen\PaymentBundle\Model\Payment\PaymentOrderInterface;
use Flowcode\UserBundle\Entity\UserInterface;

class PaymentInfoBuilder implements PaymentInfoBuilderInterface
{
    /**
     * @param Request $request
     * @return PaymentInfo
     */
    public function buildFromRequest(Request $request)
    {
        $paymentInfo = new PaymentInfo();
        var_dump($request->query);
        $paymentInfo->setTransactionId($request->query->get('id'));
        $paymentInfo->setPaymentReference($request->query->get('topic'));

        return $paymentInfo;
    }

    /**
     * @param $order
     * @param $user
     * @param null $method
     * @return PaymentInfo
     */
    public function buildForButtonGateway(PaymentOrderInterface $order, UserInterface $user, $method = null)
    {
        $paymentInfo = new PaymentInfo();
        $paymentInfo->setUnitPrice($order->getTotal());
        $paymentInfo->setCustomerMail($user->getEmail());
        //TODO: Improve currency
        $paymentInfo->setCurrencyId('USD');
        $paymentInfo->setOrderId($order->getId());

        //TODO: Add each items to paymentInfo
        $paymentInfoItem = new PaymentInfoItem();
        $paymentInfoItem->setItemId($order->getId());
        //TODO: Improve currency
        $paymentInfoItem->setCurrencyId('USD');
        $paymentInfoItem->setTitle('Pedido número: '. $order->getId());
        $paymentInfoItem->setQuantity(1);
        $paymentInfoItem->setUnitPrice($order->getTotal());

        $paymentInfo->addPaymentInfoItem($paymentInfoItem);

        return $paymentInfo;
    }
}