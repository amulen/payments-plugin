<?php
namespace Amulen\PaymentBundle\Service\Paypal;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Amulen\PaymentBundle\Model\Gateway\Paypal\Setting;
use Amulen\PaymentBundle\Model\Gateway\SubscriptionButtonGateway;
use Symfony\Component\Routing\Router;
use Amulen\SettingsBundle\Model\SettingRepository;
use DateTime;
use PayPal\Auth\OAuthTokenCredential;
use PayPal\Api\Payer;
use PayPal\Api\FlowConfig;
use PayPal\Api\Presentation;
use PayPal\Api\InputFields;
use PayPal\Rest\ApiContext;
use PayPal\Api\Agreement;
use PayPal\Api\Plan;
use Amulen\PaymentBundle\Model\Payment\SubscriptionInfo;

class PaypalSubscriptionButtonGateway implements SubscriptionButtonGateway
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

    public function getLinkUrl(SubscriptionInfo $subscriptionInfo)
    {
        $startDate = new \DateTime('now', new \Datetimezone('UTC'));
        $startDate->modify('+1 hour');
        try {
            $agreement = new Agreement();
            $agreement->setName($subscriptionInfo->getName())
                ->setDescription($subscriptionInfo->getDescription())
                ->setStartDate($startDate->format(DateTime::ISO8601));

            $plan = new Plan();
            $plan->setId($subscriptionInfo->getPlanId());
            $agreement->setPlan($plan);
            $payer = new Payer();
            $payer->setPaymentMethod('paypal');
            $agreement->setPayer($payer);

            $merchantPreferences = new \PayPal\Api\MerchantPreferences();
            $merchantPreferences->setReturnUrl($subscriptionInfo->getReturnUrl());
            $merchantPreferences->setCancelUrl($subscriptionInfo->getCancelUrl());
            $agreement->setOverrideMerchantPreferences($merchantPreferences);
            $agreement = $agreement->create($this->apiContext);
            return $agreement->getApprovalLink();
        } catch (\PayPal\Exception\PayPalConnectionException $ex) {
            var_dump($ex->getData());
            throw new \InvalidArgumentException('subscription:plan:invalid');
        } catch (\Exception $ex) {
            throw new \InvalidArgumentException('subscription:plan:invalid');
        }
    }

    public function confirmSubscription($token)
    {
        if ($token != null) {
            try {
                $agreement = new Agreement();
                $agreement = $agreement->execute($token, $this->apiContext);
                var_dump($agreement);
                return $agreement->getId();
            } catch (\PayPal\Exception\PayPalConnectionException $ex) {
                throw new \InvalidArgumentException('payment:confirm:invalid');
            }
        } else {
            throw new \InvalidArgumentException('payment:confirm:invalid');
        }
    }

    public function validateSubscription(SubscriptionInfo $subscriptionInfo)
    {
        
    }
}
