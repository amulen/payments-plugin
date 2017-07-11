<?php

namespace Amulen\PaymentBundle\Service\Paypal;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Amulen\PaymentBundle\Event\ProcessedPaymentEvent;
use Amulen\PaymentBundle\Model\Exception\GatewayException;
use Amulen\PaymentBundle\Model\Gateway\Paypal\Setting;
use Amulen\PaymentBundle\Model\Gateway\PaymentButtonGateway;
use Amulen\PaymentBundle\Model\Gateway\Response;
use Amulen\PaymentBundle\Model\Payment\Status;
use Symfony\Component\Routing\Router;
use Amulen\SettingsBundle\Model\SettingRepository;
use PayPal\Auth\OAuthTokenCredential;
use PayPal\Api\Payer;
use PayPal\Api\Item;
use PayPal\Api\Transaction;
use PayPal\Api\ItemList;
use PayPal\Api\Amount;
use PayPal\Api\RedirectUrls;
use PayPal\Api\Payment;
use PayPal\Api\WebProfile;
use PayPal\Api\FlowConfig;
use PayPal\Api\Presentation;
use PayPal\Api\InputFields;
use PayPal\Api\PayerInfo;
use PayPal\Rest\ApiContext;
use PayPal\Api\PaymentOptions;

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
    private $apiContext;

    /**
     * PaymentService constructor.
     * @param Router $router
     */
    public function __construct(Router $router, ContainerInterface $container, $logger, SettingRepository $settingRepository) {
        $this->router = $router;
        $this->container = $container;
        $this->logger = $logger;
        $this->settings = $settingRepository;
        $this->apiContext = new ApiContext(
                new OAuthTokenCredential(
                $this->settings->get(Setting::CLIENT_ID), $this->settings->get(Setting::CLIENT_SECRET)
                )
        );
        $apiConfig = array();
        if ($this->settings->get(Setting::ENVIRONMENT_SANDBOX)) {
            $apiConfig['mode'] = 'sandbox';
        } else {
            $apiConfig['mode'] = 'live';
        }
        $this->apiContext->setConfig($apiConfig);
    }

    public function getLinkUrl($paymentInfo) {
        $experiencedProfile = $this->createExperiencedProfile($paymentInfo);
        $payerInfo = new PayerInfo();
        $payerInfo->setEmail($paymentInfo->getCustomerMail());
        $payer = new Payer();
        $payer->setPaymentMethod("paypal");
        $payer->setPayerInfo($payerInfo);

        $items = array();
        foreach ($paymentInfo->getPaymentInfoItems() as $currentItem) {
            $item = new Item();
            $item->setName($currentItem->getTitle())
                    ->setCurrency('USD')
                    ->setQuantity($currentItem->getQuantity())
                    ->setSku($paymentInfo->getOrderId())
                    ->setPrice($currentItem->getUnitPrice());
            array_push($items, $item);
        }


        $itemList = new ItemList();
        $itemList->setItems($items);

        $amount = new Amount();
        $amount->setCurrency("USD")
                ->setTotal($paymentInfo->getUnitPrice());

        $paymentOptions = new PaymentOptions();
        $paymentOptions->setAllowedPaymentMethod("IMMEDIATE_PAY");
        $transaction = new Transaction();
        $transaction->setAmount($amount)
                ->setItemList($itemList)
                ->setDescription($paymentInfo->getDescription())
                ->setPaymentOptions($paymentOptions)
                ->setNotifyUrl($this->container->getParameter('back_url_payment_notify'));

        $redirectUrls = new RedirectUrls();
        $redirectUrls->setReturnUrl($this->container->getParameter('front_url_payment_success', [], Router::ABSOLUTE_URL))
                ->setCancelUrl($this->container->getParameter('front_url_payment_error', [], Router::ABSOLUTE_URL));
        $payment = new Payment();
        $payment->setIntent("sale")
                ->setPayer($payer)
                ->setRedirectUrls($redirectUrls)
                ->setTransactions(array($transaction))
                ->setExperienceProfileId($experiencedProfile->getId());

        $payment->create($this->apiContext);
        $approvalUrl = $payment->getApprovalLink();
        return $approvalUrl;
    }

    private function createExperiencedProfile($paymentInfo) {
        $this->cleanWebsProfile();
        $websProfile = WebProfile::get_list($this->apiContext);
        if (empty($websProfile)) {
            $flowConfig = new FlowConfig();
            $flowConfig->setUserAction('commit');

            $presentation = new Presentation();
            $presentation->setBrandName($paymentInfo->getBrandName())
                    ->setLogoImage($paymentInfo->getBrandLogo());
            $inputFields = new InputFields();
            $inputFields->setAllowNote(false)
                    ->setNoShipping(1);
            $newWebProfile = new \PayPal\Api\WebProfile();
            $newWebProfile->setName('Profile ' . $paymentInfo->getBrandName())
                    ->setPresentation($presentation)
                    ->setFlowConfig($flowConfig)
                    ->setInputFields($inputFields)
                    ->setTemporary(false);
            $webProfile = $newWebProfile->create($this->apiContext);
            return $webProfile;
        }
        return $websProfile[0];
    }

    public function validatePayment($paymentInfo): Response {
        $response = new Response();
        $response->setMessage('OK');
        $response->setStatus(Status::APPROVED);
        $response->setOrderId($paymentInfo->getOrderId());

        return $response;
    }

    private function cleanWebsProfile() {
        $websProfile = WebProfile::get_list($this->apiContext);
        foreach ($websProfile as $webProfileJson) {
            $webProfileJson->delete($this->apiContext);
        }
    }

}
