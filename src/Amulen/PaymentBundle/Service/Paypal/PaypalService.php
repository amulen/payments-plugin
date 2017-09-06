<?php
namespace Amulen\PaymentBundle\Service\Paypal;

use Doctrine\ORM\EntityManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Amulen\PaymentBundle\Model\Gateway\Paypal\Setting;
use Symfony\Component\Routing\Router;
use Amulen\SettingsBundle\Model\SettingRepository;
use PayPal\Auth\OAuthTokenCredential;
use PayPal\Rest\ApiContext;
use Amulen\PaymentBundle\Model\Gateway\Paypal\PaypalPlan;
use PayPal\Api\Plan;
use PayPal\Api\PaymentDefinition;
use PayPal\Api\Currency;
use PayPal\Exception\PayPalConnectionException;
use PayPal\Api\MerchantPreferences;
use PayPal\Api\Patch;
use PayPal\Common\PayPalModel;
use PayPal\Api\PatchRequest;

class PaypalService implements PaypalServiceInterface
{

    protected $container;
    protected $em;

    public function __construct(EntityManager $em, ContainerInterface $container, Router $router, $logger, SettingRepository $settingRepository)
    {
        $this->em = $em;
        $this->container = $container;
        $this->router = $router;
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

    public function verifyNotification($data)
    {
        return;
    }

    function createPlan(PaypalPlan $paypalPlan)
    {
        $plan = new Plan();
        $plan->setName($paypalPlan->getName())
            ->setDescription($paypalPlan->getDescription())
            ->setType('INFINITE');
        $paymentsDefinition = array();
        foreach ($paypalPlan->getPayments() as $currentPaypalPayment) {
            $paymentDefinition = new PaymentDefinition();
            $paymentDefinition->setName($currentPaypalPayment->getName())
                ->setType($currentPaypalPayment->getType())
                ->setFrequency('Month')
                ->setFrequencyInterval($currentPaypalPayment->getFrequencyInterval())
                ->setCycles($currentPaypalPayment->getCycles())
                ->setAmount(new Currency(array('value' => $currentPaypalPayment->getAmount(), 'currency' => 'USD')));
            array_push($paymentsDefinition, $paymentDefinition);
        }
        $returnUrl = $this->container->getParameter('front_url_payment_success', [], Router::ABSOLUTE_URL);
        $cancelUrl = $this->container->getParameter('front_url_payment_error', [], Router::ABSOLUTE_URL);
        $merchantPreferences = new MerchantPreferences();
        $merchantPreferences
            ->setReturnUrl($returnUrl)
            ->setCancelUrl($cancelUrl)
            ->setAutoBillAmount("yes")
            ->setInitialFailAmountAction("CONTINUE")
            ->setMaxFailAttempts("0");
        $plan->setPaymentDefinitions($paymentsDefinition);
        $plan->setMerchantPreferences($merchantPreferences);
        try {
            $data = $plan->create($this->apiContext);
        } catch (PayPalConnectionException $ex) {
            echo $ex->getCode(); // Prints the Error Code
            echo $ex->getData(); // Prints the detailed error message 
            die($ex);
        } catch (Exception $ex) {
            die($ex);
        }

        $patch = new Patch();
        $value = new PayPalModel('{
	       "state":"ACTIVE"
	     }');
        $patch->setOp('replace')->setPath('/')->setValue($value);
        $patchRequest = new PatchRequest();
        $patchRequest->addPatch($patch);
        $plan->update($patchRequest, $this->apiContext);
        $plan = Plan::get($plan->getId(), $this->apiContext);
        return $plan->getId();
    }


}
