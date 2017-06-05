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
        $items = $this->itemsToArray($paymentInfo->getPaymentInfoItems());
        $preference_data = array(
            "id" => $paymentInfo->getOrderId(),
            "items" => $items,
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
            $setting = $this->settings->get(Setting::KEY_ENVIRONMENT);
            if ($setting) {
                $value = strtolower(trim($setting));
                if ($value == 'yes' || $value == 'si' || $value == 'on' || $value == 'true') {
                    $returnUrl = $preference["response"]["sandbox_init_point"];
                }
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
            $merchantOrderInfo = $mpPaymentInfo["response"]["collection"];
        }

        if ($merchantOrderInfo['status'] != 'approved') {
            return false;
        }

        if ($merchantOrderInfo['status'] == 'approved') {
            $order = $this->orderRepository->find($merchantOrderInfo["external_reference"]);

            // Totally paid. Release your item.
            $status = Status::APPROVED;
            $processedPaymentEvent = new ProcessedPaymentEvent($status);
            $processedPaymentEvent->setOrderId($order->getId());

            $this->eventDispatcher->dispatch(ProcessedPaymentEvent::NAME, $processedPaymentEvent);

            return true;
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

    private function itemsToArray($items)
    {
        $response = [];

        foreach ($items as $item) {
            $element['id'] = $item->getItemId() ? $item->getItemId() : null;
            $element['title'] = $item->getTitle() ? $item->getTitle() : null;
            $element['description'] = $item->getDescription() ? $item->getDescription() : null;
            $element['unit_price'] = $item->getUnitPrice() ? $item->getUnitPrice() : null;
            $element['quantity'] = $item->getQuantity() ? $item->getQuantity() : null;
            $element['currency_id'] = $item->getCurrencyId() ? $item->getCurrencyId() : null;
            array_push($response, $element);
        }
        return $response;
    }

}