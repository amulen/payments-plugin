<?php

namespace Amulen\PaymentBundle\Service\Paypal;

use Symfony\Component\DependencyInjection\ContainerInterface;
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
use Amulen\PaymentBundle\Model\Payment\PaymentInfo;

/**
 * Paypal buttons payments gateway.
 */
class PaypalPaymentButtonGateway implements PaymentButtonGateway {

    protected $container;

    /**
     * @var Router
     */
    private $router;

    /**
     * @var string
     */
    protected $logger;

    /**
     * @var SettingRepository
     */
    private $settings;

    /**
     * PaymentService constructor.
     * @param Router $router
     */
    public function __construct(Router $router, ContainerInterface $container, $logger, SettingRepository $settingRepository) {
        $this->router = $router;
        $this->container = $container;
        $this->logger = $logger;
        $this->settings = $settingRepository;
    }

    public function getLinkUrl(PaymentInfo $paymentInfo) {
        $urlPaypal = 'https://www.paypal.com/cgi?bin/webscr?cmd=_express-checkout&token=';
        $config = array(
            "mode" => "live",
            "acct1.UserName" => $this->settings->get(Setting::KEY_USERNAME),
            "acct1.Password" => $this->settings->get(Setting::KEY_PASSWORD),
            "acct1.Signature" => $this->settings->get(Setting::KEY_SIGNATURE)
        );
        if ($this->settings->get(Setting::ENVIRONMENT_SANDBOX)) {
            $config['mode'] = 'sandbox';
            $urlPaypal = 'https://www.sandbox.paypal.com/webscr?cmd=_express-checkout&token=';
        }

        $paymentInfoItem = $paymentInfo->getPaymentInfoItems()[0];

        $paypalService = new PayPalAPIInterfaceServiceService($config);

        $orderTotal = new BasicAmountType();
        $orderTotal->currencyID = $paymentInfo->getCurrencyId();
        $orderTotal->value = $paymentInfo->getUnitPrice();

        $itemDetails = new PaymentDetailsItemType();
        $itemDetails->Name = $paymentInfoItem->getTitle();
        $itemDetails->Amount = $paymentInfoItem->getUnitPrice();
        $itemDetails->Quantity = $paymentInfoItem->getQuantity();
        $itemDetails->ItemCategory = 'Digital';

        $paymentDetails = new PaymentDetailsType();
        $paymentDetails->PaymentDetailsItem[0] = $itemDetails;
        $paymentDetails->OrderTotal = $orderTotal;
        $paymentDetails->ItemTotal = $orderTotal;
        $paymentDetails->PaymentAction = 'Sale';
        $paymentDetails->NotifyURL = $this->router->generate('amulen_payment_async_notification', ['gatewayId' => Setting::GATEWAY_ID], Router::ABSOLUTE_URL);


        $setECReqDetails = new SetExpressCheckoutRequestDetailsType();
        $setECReqDetails->PaymentDetails[0] = $paymentDetails;
        $setECReqDetails->ReturnURL = $this->container->getParameter('front_url_payment_success', [], Router::ABSOLUTE_URL);
        $setECReqDetails->CancelURL = $this->container->getParameter('front_url_payment_error', [], Router::ABSOLUTE_URL);
        $setECReqDetails->NoShipping = 1;
        $setECReqDetails->ReqConfirmShipping = 0;
        $setECReqDetails->BrandName = 'Cloudlance';

        $setECReqType = new SetExpressCheckoutRequestType();
        $setECReqType->SetExpressCheckoutRequestDetails = $setECReqDetails;
        $setECReq = new SetExpressCheckoutReq();
        $setECReq->SetExpressCheckoutRequest = $setECReqType;

        $setECResponse = $paypalService->SetExpressCheckout($setECReq);

        $token = $setECResponse->Token;
        $AcK = $setECResponse->Token;

        $paypalUrlToken = $urlPaypal . $token;
        var_dump($token);
        var_dump($AcK);
        var_dump($this->router->generate('amulen_payment_async_notification', ['gatewayId' => Setting::GATEWAY_ID], Router::ABSOLUTE_URL));
        var_dump($paypalUrlToken);
        return $paypalUrlToken;
    }

    public function validatePayment(PaymentInfo $paymentInfo): Response {
        $response = new Response();
        $response->setMessage('OK');
        $response->setStatus(Status::APPROVED);
        $response->setOrderId($paymentInfo->getOrderId());

        return $response;
    }

}
