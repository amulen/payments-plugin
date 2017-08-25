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
use PayPal\Api\PaymentExecution;
use PayPal\Api\Address;

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
    private $apiContext;
    private $validCountryCodes;

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
        $this->validCountryCodes = array('AL', 'DZ', 'AD', 'AO', 'AI', 'AG', 'AR', 'AM', 'AW', 'AU', 'AT', 'AZ', 'BS', 'BH', 'BB', 'BY', 'BE', 'BZ', 'BJ', 'BM', 'BT', 'BO', 'BA', 'BW', 'BR', 'VG', 'BN', 'BG', 'BF', 'BI', 'KH', 'CM', 'CA', 'CV', 'KY', 'TD', 'CL', 'C2', 'CO', 'KM', 'CG', 'CD', 'CK', 'CR', 'CI', 'HR', 'CY', 'CZ', 'DK', 'DJ', 'DM', 'DO', 'EC', 'EG', 'SV', 'ER', 'EE', 'ET', 'FK', 'FO', 'FJ', 'FI', 'FR', 'GF', 'PF', 'GA', 'GM', 'GE', 'DE', 'GI', 'GR', 'GL', 'GD', 'GP', 'GT', 'GN', 'GW', 'GY', 'HN', 'HK', 'HU', 'IS', 'IN', 'ID', 'IE', 'IL', 'IT', 'JM', 'JP', 'JO', 'KZ', 'KE', 'KI', 'KW', 'KG', 'LA', 'LV', 'LS', 'LI', 'LT', 'LU', 'MK', 'MG', 'MW', 'MY', 'MV', 'ML', 'MT', 'MH', 'MQ', 'MR', 'MU', 'YT', 'MX', 'FM', 'MD', 'MC', 'MN', 'ME', 'MS', 'MA', 'MZ', 'NA', 'NR', 'NP', 'NL', 'NC', 'NZ', 'NI', 'NE', 'NG', 'NU', 'NF', 'NO', 'OM', 'PW', 'PA', 'PG', 'PY', 'PE', 'PH', 'PN', 'PL', 'PT', 'QA', 'RE', 'RO', 'RU', 'RW', 'WS', 'SM', 'ST', 'SA', 'SN', 'RS', 'SC', 'SL', 'SG', 'SK', 'SI', 'SB', 'SO', 'ZA', 'KR', 'ES', 'LK', 'SH', 'KN', 'LC', 'PM', 'VC', 'SR', 'SJ', 'SZ', 'SE', 'CH', 'TW', 'TJ', 'TZ', 'TH', 'TG', 'TO', 'TT', 'TN', 'TM', 'TC', 'TV', 'UG', 'UA', 'AE', 'GB', 'US', 'UY', 'VU', 'VA', 'VE', 'VN', 'WF', 'YE', 'ZM', 'ZW');
    }

    public function getLinkUrl($paymentInfo)
    {

        try {
            $experiencedProfile = $this->createExperiencedProfile($paymentInfo);

            $payerInfo = new PayerInfo();
            $payerInfo->setEmail($paymentInfo->getCustomerMail());
            $payerInfo->setCountryCode('AR');

            $payer = new Payer();
            $payer->setPaymentMethod("paypal");

            $items = array();
            foreach ($paymentInfo->getPaymentInfoItems() as $currentItem) {
                $item = new Item();
                $item->setName($currentItem->getDescription())
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
                ->setNotifyUrl($this->container->getParameter('back_url_payment_notify'))
                ->setCustom($paymentInfo->getCustomerId());

            $redirectUrls = new RedirectUrls();
            $redirectUrls->setReturnUrl($paymentInfo->getReturnUrl())
                ->setCancelUrl($paymentInfo->getCancelUrl());
            $payment = new Payment();
            $payment->setIntent("sale")
                ->setPayer($payer)
                ->setRedirectUrls($redirectUrls)
                ->setTransactions(array($transaction))
                ->setExperienceProfileId($experiencedProfile->getId());
            $payment->create($this->apiContext);
            $approvalUrl = $payment->getApprovalLink();
            return $approvalUrl;
        } catch (\Exception $ex) {
            return $ex->getMessage();
        }
    }

    public function confirmPayment($paymentInfo)
    {
        $paymentId = $paymentInfo->getPaymentId();
        $payerId = $paymentInfo->getPayerId();
        if ($paymentId != null && $payerId != null) {
            try {
                $payment = Payment::get($paymentId, $this->apiContext);
                $execution = new PaymentExecution();
                $execution->setPayerId($payerId);
                return $payment->execute($execution, $this->apiContext);
            } catch (\PayPal\Exception\PayPalConnectionException $ex) {
                throw new \InvalidArgumentException('payment:confirm:invalid');
            }
        } else {
            throw new \InvalidArgumentException('payment:confirm:invalid');
        }
    }

    private function createExperiencedProfile($paymentInfo)
    {
        $flowConfig = new FlowConfig();
        $flowConfig->setUserAction('commit');
        $flowConfig->setLandingPageType("Billing");
        $presentation = new Presentation();
        $presentation->setBrandName($paymentInfo->getBrandName())
            ->setLogoImage($paymentInfo->getBrandLogo());

        $presentation->setLocaleCode('US');

        $inputFields = new InputFields();
        $inputFields->setAllowNote(false)
            ->setNoShipping(1);
        $newWebProfile = new \PayPal\Api\WebProfile();
        $newWebProfile->setName('Profile ' . $paymentInfo->getBrandName() . strtotime('now'))
            ->setPresentation($presentation)
            ->setFlowConfig($flowConfig)
            ->setInputFields($inputFields)
            ->setTemporary(true);
        $webProfile = $newWebProfile->create($this->apiContext);
        return $webProfile;
    }

    public function validatePayment($paymentInfo): Response
    {
        
    }
}
