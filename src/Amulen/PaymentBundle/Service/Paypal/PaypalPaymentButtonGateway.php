<?php
namespace Amulen\PaymentBundle\Service\Paypal;

use Amulen\PaymentBundle\Event\ProcessedPaymentEvent;
use Amulen\PaymentBundle\Model\Exception\GatewayException;
use Amulen\PaymentBundle\Model\Gateway\Paypal\Setting;
use Amulen\PaymentBundle\Model\Gateway\PaymentButtonGateway;
use Amulen\PaymentBundle\Model\Gateway\Response;
use Amulen\PaymentBundle\Model\Payment\Status;
use Amulen\ShopBundle\Repository\ProductOrderRepository;
use Symfony\Component\Routing\Router;
use Amulen\SettingsBundle\Model\SettingRepository;
use PayPal\CoreComponentTypes\BasicAmountType;
use PayPal\PayPalAPI\SetExpressCheckoutRequestType;
use PayPal\PayPalAPI\SetExpressCheckoutReq;
use PayPal\Service\PayPalAPIInterfaceServiceService;
use PayPal\EBLBaseComponents\PaymentDetailsType;
use PayPal\EBLBaseComponents\PaymentDetailsItemType;
use PayPal\EBLBaseComponents\SetExpressCheckoutRequestDetailsType;

/**
 * Paypal buttons payments gateway.
 */
class PaypalPaymentButtonGateway implements PaymentButtonGateway
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
    public function __construct(Router $router, $logger, SettingRepository $settingRepository)
    {
        $this->router = $router;
        $this->logger = $logger;
        $this->settings = $settingRepository;
    }

    /**
     * @inheritdoc
     */
    public function getLinkUrl($paymentInfo)
    {
        $config = array(
            'mode' => 'sandbox',
            "acct1.UserName" => $this->settings->get(Setting::KEY_USERNAME),
            "acct1.Password" => $this->settings->get(Setting::KEY_PASSWORD),
            "acct1.Signature" => $this->settings->get(Setting::KEY_SIGNATURE)
        );

        $paypalService = new PayPalAPIInterfaceServiceService($config);

        $returnUrl = "http://www.google.com.ar/DGdoExpressCheckout.php";
        $cancelUrl = "http://www.google.com.ar/DGsetEC.html.php";

        $orderTotal = new BasicAmountType();
        $orderTotal->currencyID = 'USD';
        $orderTotal->value = $paymentInfo->getUnitPrice();
        $itemDetails = new PaymentDetailsItemType();
        $itemDetails->Name = 'sample item';
        $itemDetails->Amount = $orderTotal;
        $itemDetails->Quantity = '1';
        $itemDetails->ItemCategory = 'Digital';

        $PaymentDetails = new PaymentDetailsType();
        $PaymentDetails->PaymentDetailsItem[0] = $itemDetails;
        $PaymentDetails->OrderTotal = $orderTotal;
        $PaymentDetails->PaymentAction = 'Sale';
        $PaymentDetails->ItemTotal = $orderTotal;

        $setECReqDetails = new SetExpressCheckoutRequestDetailsType();
        $setECReqDetails->PaymentDetails[0] = $PaymentDetails;
        $setECReqDetails->CancelURL = $cancelUrl;
        $setECReqDetails->ReturnURL = $returnUrl;

        $setECReqType = new SetExpressCheckoutRequestType();
        $setECReqType->SetExpressCheckoutRequestDetails = $setECReqDetails;
        $setECReq = new SetExpressCheckoutReq();
        $setECReq->SetExpressCheckoutRequest = $setECReqType;


        $setECResponse = $paypalService->SetExpressCheckout($setECReq);
        $token = $setECResponse->Token;
        $payPalURL = 'https://www.sandbox.paypal.com/incontext?token=' . $token;

        return $payPalURL;
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
            $msg = 'Orden no aprovada con Id (MP): ' . $paymentInfo->getTransactionId() . '. Estado de orden en MP: ' . $merchantOrderInfo['status'];
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
            $msg = 'Orden aprovada con Id (MP): ' . $paymentInfo->getTransactionId() . '. Estado de orden en MP: ' . $merchantOrderInfo['status'];
            $this->logger->info($msg);
            $msg = 'External reference (Id orden de la tienda): ' . $merchantOrderInfo["external_reference"];
            $this->logger->info($msg);

            return true;
        }
        $msg = 'Orden fallo. Id (MP): ' . $paymentInfo->getTransactionId() . '. Estado de orden en MP: ' . $merchantOrderInfo['status'];
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
}
