<?php

namespace Amulen\PaymentBundle\Service\Nps;

use Amulen\NpsBundle\Model\Client\SoapClient;
use Amulen\PaymentBundle\Model\Gateway\Nps\Setting;
use Amulen\SettingsBundle\Model\SettingRepository;

/**
 * SoapClientFactory
 */
class SoapClientFactory
{
    public function __construct($logger, SettingRepository $settingRepository)
    {
        $this->logger = $logger;
        $this->settings = $settingRepository;
    }

    public function npsSoapClient()
    {
        // SoapClient option['trace'] -> "The trace option enables tracing of request so faults can be backtraced"
        $client = new SoapClient($this->settings->get(Setting::KEY_WSDL_URL), array('trace' => 1));
        $client->setMerchantId($this->settings->get(Setting::KEY_MERCHANT_ID));
        $client->setSecretKey($this->settings->get(Setting::KEY_SECRET_KEY));
        $client->setLogger($this->logger);
        return $client;
    }
}