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
     * @var string
     */
    protected $logger;

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
    public function __construct(Router $router, $logger, $eventDispatcher, SettingRepository $settingRepository, ProductOrderRepository $orderRepository)
    {
        $this->router = $router;
        $this->logger = $logger;
        $this->eventDispatcher = $eventDispatcher;
        $this->settings = $settingRepository;
        $this->orderRepository = $orderRepository;
    }

    /**
     * @inheritdoc
     */
    public function getLinkUrl($paymentInfo)
    {
        $excluded = $this->excludedPaymentTypes();
        $items = $this->itemsToArray($paymentInfo->getPaymentInfoItems());
        $preference_data = array(
            "id" => $paymentInfo->getOrderId(),
            "external_reference" => $paymentInfo->getOrderId(),
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
            ),
            "payment_methods" => array(
                "excluded_payment_types" => $excluded
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
            $msg = 'Orden no aprovada con Id (MP): '.$paymentInfo->getTransactionId().'. Estado de orden en MP: '. $merchantOrderInfo['status'];
            $this->logger->critical($msg);
            return false;
        }

        if ($merchantOrderInfo['status'] == 'approved' && $merchantOrderInfo["external_reference"]) {
            $order = $this->orderRepository->find($merchantOrderInfo["external_reference"]);

            // Totally paid. Release your item.
            $status = Status::APPROVED;
            $processedPaymentEvent = new ProcessedPaymentEvent($status);
            $processedPaymentEvent->setOrderId($order->getId());

            $this->eventDispatcher->dispatch(ProcessedPaymentEvent::NAME, $processedPaymentEvent);

            /* Log */
            $msg = 'Orden aprovada con Id (MP): '.$paymentInfo->getTransactionId().'. Estado de orden en MP: '. $merchantOrderInfo['status'];
            $this->logger->info($msg);
            $msg = 'External reference (Id orden de la tienda): '.$merchantOrderInfo["external_reference"];
            $this->logger->info($msg);

            return true;
        }
        $msg = 'Orden fallo. Id (MP): '.$paymentInfo->getTransactionId().'. Estado de orden en MP: '. $merchantOrderInfo['status'];
        $this->logger->critical($msg);
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

    private function excludedPaymentTypes()
    {
        $excludedTypes = $this->settings->getByKey(Setting::KEY_EXCLUDED_PAYMENT_TYPES);
        $response = [];
        if($excludedTypes){
            foreach ($excludedTypes as $item) {
                $element['id'] = $item;
                array_push($response, $element);
            }
        }
        return $response;
    }

}