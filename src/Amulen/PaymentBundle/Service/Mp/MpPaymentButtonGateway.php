<?php

namespace Amulen\PaymentBundle\Service\Mp;

use Amulen\PaymentBundle\Event\ProcessedPaymentEvent;
use Amulen\PaymentBundle\Model\Exception\GatewayException;
use Amulen\PaymentBundle\Model\Gateway\Mp\Setting;
use Amulen\PaymentBundle\Model\Gateway\PaymentButtonGateway;
use Amulen\PaymentBundle\Model\Gateway\Response;
use Amulen\PaymentBundle\Model\Payment\Status;
use Amulen\SettingsBundle\Model\SettingRepository;
use Amulen\ShopBundle\Repository\ProductOrderRepository;
use Symfony\Component\Routing\Router;
use MercadoPagoException;
use MP;

/**
 * Mp buttons payments gateway.
 */
class MpPaymentButtonGateway implements PaymentButtonGateway
{

    /**
     * @var PaymentService
     */
    private $mpSdk;

    /**
     * @var Router
     */
    private $router;

    /**
     * @var mixed
     */
    private $eventDispatcher;

    /**
     * @var SettingRepository
     */
    private $settings;

    /**
     * @var ProductOrderRepository
     */
    private $orderRepository;

    /**
     * PaymentService constructor.
     * @param Router $router
     */
    public function __construct(Router $router, $eventDispatcher, SettingRepository $settingRepository, ProductOrderRepository $orderRepository)
    {
        $this->router = $router;
        $this->eventDispatcher = $eventDispatcher;
        $this->settings = $settingRepository;
        $this->orderRepository = $orderRepository;
    }

    /**
     * @inheritdoc
     */
    public function getLinkUrl($paymentInfo)
    {
        $preference_data = array(
            "id" => $paymentInfo->getOrderId(),
            "items" => array(
                array(
                    "title" => "Test Modified",
                    "quantity" => 1,
                    "currency_id" => Setting::CURRENCY_PESO,
                    "unit_price" => $paymentInfo->getUnitPrice()
                )
            ),
            // Volver al sitio del vendedor
            "back_urls" => array(
                "success" => $this->router->generate('product_order_confirmed', [], Router::ABSOLUTE_URL),
                "pending" => $this->router->generate('product_order_confirmed', [], Router::ABSOLUTE_URL),
                "failure" => $this->router->generate('order', [], Router::ABSOLUTE_URL),
            ),
            // URL de notificacion
            "notification_url" => $this->router->generate('amulen_payment_async_notification', ['gatewayId' => Setting::GATEWAY_ID], Router::ABSOLUTE_URL),
            "payer" => array(
                "email" => $paymentInfo->getCustomerMail()
            )
        );
        try {
            $preference = $this->getMpSdk()->create_preference($preference_data);

            $returnUrl = $preference["response"]["init_point"];

            /* Sandbox Mode */
            if($this->settings->get(Setting::KEY_ENVIRONMENT)){
                $returnUrl = $preference["response"]["sandbox_init_point"];
            }

            return $returnUrl;

        } catch (MercadoPagoException $e) {
            throw new GatewayException($e->getMessage());
        }
    }

    /**
     * @inheritdoc
     */
    public function validatePayment($paymentInfo)
    {
        if ((!$paymentInfo->getTransactionId() && !$paymentInfo->getPaymentReference()) || !ctype_digit($paymentInfo->getTransactionId())) {
            return false;
        }

        $merchantOrderInfo = [];
        // Get the payment and the corresponding merchant_order reported by the IPN.
        if ($paymentInfo->getPaymentReference() == 'payment') {
            $mpPaymentInfo = $this->getMpSdk()->get("/collections/notifications/" . $paymentInfo->getTransactionId());
            $merchantOrderInfo = $this->getMpSdk()->get("/merchant_orders/" . $mpPaymentInfo["response"]["collection"]["merchant_order_id"]);
            // Get the merchant_order reported by the IPN.
        } else if ($paymentInfo->getPaymentReference() == 'merchant_order') {
            $merchantOrderInfo = $this->getMpSdk()->get("/merchant_orders/" . $paymentInfo->getTransactionId());
        }

        if ($merchantOrderInfo['status'] != 200) {
            return false;
        }

        if ($merchantOrderInfo["status"] == 200) {
            $order = $this->orderRepository->find($merchantOrderInfo['response']['external_reference']);

            $paidAmount = 0;
            foreach ($merchantOrderInfo["response"]["payments"] as $payment) {
                if ($payment['status'] == 'approved') {
                    $paidAmount += $payment['transaction_amount'];
                }
            }

            if ($paidAmount >= $merchantOrderInfo["response"]["total_amount"]) {
                // Totally paid. Release your item.
                $status = Status::APPROVED;
                $processedPaymentEvent = new ProcessedPaymentEvent($status);
                $processedPaymentEvent->setOrderId($order->getId());

                $this->eventDispatcher->dispatch(ProcessedPaymentEvent::NAME, $processedPaymentEvent);

                return true;
            }
        }

        return false;
    }

    /**
     * @return MP
     */
    public function getMpSdk()
    {
        if (!$this->mpSdk) {
            $this->mpSdk = new MP($this->settings->get(Setting::KEY_MERCHANT_ID), $this->settings->get(Setting::KEY_SECRET_KEY));
        }
        return $this->mpSdk;
    }


}