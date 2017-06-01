<?php

namespace Amulen\PaymentBundle\Service\Mp;

use Amulen\PaymentBundle\Model\Exception\GatewayException;
use Amulen\PaymentBundle\Model\Gateway\Mp\Setting;
use Amulen\PaymentBundle\Model\Gateway\PaymentButtonGateway;
use Amulen\PaymentBundle\Model\Gateway\Response;
use Amulen\PaymentBundle\Model\Payment\Status;
use Amulen\SettingsBundle\Model\SettingRepository;
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
     * @var SettingRepository
     */
    private $settings;

    /**
     * PaymentService constructor.
     * @param Router $router
     */
    public function __construct(Router $router, SettingRepository $settingRepository)
    {
        $this->router = $router;
        $this->settings = $settingRepository;
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
            http_response_code(400);
            return;
        }

        $merchant_order_info = [];
        // Get the payment and the corresponding merchant_order reported by the IPN.
        if ($paymentInfo->getPaymentReference() == 'payment') {
            $payment_info = $this->getMpSdk()->get("/collections/notifications/" . $paymentInfo->getTransactionId());
            $merchant_order_info = $this->getMpSdk()->get("/merchant_orders/" . $payment_info["response"]["collection"]["merchant_order_id"]);
            // Get the merchant_order reported by the IPN.
        } else if ($paymentInfo->getPaymentReference() == 'merchant_order') {
            $merchant_order_info = $this->getMpSdk()->get("/merchant_orders/" . $paymentInfo->getTransactionId());
        }

        if ($merchant_order_info["status"] == 200) {
            // If the payment's transaction amount is equal (or bigger) than the merchant_order's amount you can release your items
            $paid_amount = 0;

            foreach ($merchant_order_info["response"]["payments"] as $payment) {
                if ($payment['status'] == 'approved') {
                    $paid_amount += $payment['transaction_amount'];
                }
            }

            if ($paid_amount >= $merchant_order_info["response"]["total_amount"]) {
                if (count($merchant_order_info["response"]["shipments"]) > 0) { // The merchant_order has shipments
                    if ($merchant_order_info["response"]["shipments"][0]["status"] == "ready_to_ship") {
                        print_r("Totally paid. Print the label and release your item.");
                    }
                } else { // The merchant_order don't has any shipments
                    print_r("Totally paid. Release your item.");
                }
            } else {
                print_r("Not paid yet. Do not release your item.");
            }
        }
        //FIXME: HTTP RESPONSE
        http_response_code(200);
        return;
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