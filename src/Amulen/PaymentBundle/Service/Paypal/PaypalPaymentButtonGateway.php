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
class PaypalPaymentButtonGateway implements PaymentButtonGateway
{

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
    public function __construct(Router $router, ContainerInterface $container, $logger, SettingRepository $settingRepository)
    {
        $this->router = $router;
        $this->container = $container;
        $this->logger = $logger;
        $this->settings = $settingRepository;
    }

    public function getLinkUrl(PaymentInfo $paymentInfo)
    {
        $config = array(
            "acct1.UserName" => $this->settings->get(Setting::KEY_USERNAME),
            "acct1.Password" => $this->settings->get(Setting::KEY_PASSWORD),
            "acct1.Signature" => $this->settings->get(Setting::KEY_SIGNATURE)
        );
        if ($this->settings->get(Setting::ENVIRONMENT_SANDBOX)) {
            $config['mode'] = 'sandbox';
        } else {
            $config['mode'] = 'live';
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

        $PaymentDetails = new PaymentDetailsType();
        $PaymentDetails->PaymentDetailsItem[0] = $itemDetails;
        $PaymentDetails->OrderTotal = $orderTotal;
        $PaymentDetails->PaymentAction = 'Sale';
        $PaymentDetails->ItemTotal = $orderTotal;

        $setECReqDetails = new SetExpressCheckoutRequestDetailsType();
        $setECReqDetails->PaymentDetails[0] = $PaymentDetails;
        $setECReqDetails->ReturnURL = $this->container->getParameter('front_url_payment_success', [], Router::ABSOLUTE_URL);
        $setECReqDetails->CancelURL = $this->container->getParameter('front_url_payment_error', [], Router::ABSOLUTE_URL);

        $setECReqType = new SetExpressCheckoutRequestType();
        $setECReqType->SetExpressCheckoutRequestDetails = $setECReqDetails;

        $setECReq = new SetExpressCheckoutReq();
        $setECReq->SetExpressCheckoutRequest = $setECReqType;
        var_dump($setECReq);

        $setECResponse = $paypalService->SetExpressCheckout($setECReq);
        var_dump($setECResponse);

        $token = $setECResponse->Token;
        //https://www.paypal.com/cgi?bin/webscr?cmd=_express-checkout&token=value_returned_by_SetExpressCheckoutResponse
        $payPalURL = 'https://www.sandbox.paypal.com/incontext?token=' . $token;
        $paypalOkUrl = 'https://www.paypal.com/cgi?bin/webscr?cmd=_express-checkout&token=' . $token;
        var_dump($payPalURL);
        var_dump($paypalOkUrl);

        return $payPalURL;
    }

    public function validatePayment(PaymentInfo $paymentInfo): Response
    {
        $response = new Response();
        $response->setMessage('OK');
        $response->setStatus(Status::APPROVED);
        $response->setOrderId($paymentInfo->getOrderId());

        return $response;
    }
}
