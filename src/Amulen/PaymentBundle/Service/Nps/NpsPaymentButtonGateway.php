<?php

namespace Amulen\PaymentBundle\Service\Nps;

use Amulen\PaymentBundle\Model\Exception\GatewayException;
use Amulen\PaymentBundle\Model\Gateway\Nps\Setting;
use Amulen\PaymentBundle\Model\Gateway\PaymentButtonGateway;
use Amulen\PaymentBundle\Model\Gateway\Response;
use Amulen\PaymentBundle\Model\Payment\Status;
use Flowcode\DashboardBundle\Service\SettingService;
use NpsSDK\ApiException;
use NpsSDK\Configuration;
use NpsSDK\Constants;
use NpsSDK\Sdk;
use Symfony\Component\Routing\Router;

/**
 * Nps buttons payments gateway.
 */
class NpsPaymentButtonGateway implements PaymentButtonGateway
{

    /**
     * @var Sdk
     */
    private $npsSdk;

    /**
     * @var Router
     */
    private $router;

    /**
     * @var SettingService
     */
    private $settings;

    /**
     * PaymentService constructor.
     * @param Router $router
     */
    public function __construct(Router $router, $settingService)
    {
        $this->router = $router;
        $this->settings = $settingService;
    }


    private function init()
    {
        //$secretKey = 'QxOXjk1EptHlJn4yBs0iJlwcPUXarXkHS6nBpmOcddNwQRsZJsHccEC1ghaXCIpf';
        //$merchantId = 'moderna';

        $secretKey = $this->settings->get(Setting::KEY_SECRET_KEY);
        $merchantId = $this->settings->get(Setting::KEY_MERCHANT_ID);
        $environment = $this->settings->get(Setting::KEY_ENVIRONMENT) ?? Setting::ENVIRONMENT_DEV;

        Configuration::environment($environment);
        Configuration::secretKey($secretKey);

    }

    /**
     * @inheritdoc
     */
    public function getLinkUrl($paymentInfo)
    {
        $this->init();

        $params = [
            'psp_Version' => '2.2',
            'psp_MerchantId' => $this->settings->get(Setting::KEY_MERCHANT_ID),
            'psp_TxSource' => 'WEB',
            'psp_MerchTxRef' => rand(200, 10000000),
            'psp_PosDateTime' => date('Y-m-d H:i:s'),
            'psp_MerchOrderId' => $paymentInfo->getOrderId(),
            'psp_Amount' => $paymentInfo->getUnitPrice(),
            'psp_NumPayments' => '1',
            'psp_Currency' => $paymentInfo->getCurrencyId(),
            'psp_Country' => 'ARG',
            'psp_Product' => $paymentInfo->getMethodId(),
            'psp_CustomerMail' => $paymentInfo->getCustomerMail(),
            'psp_ReturnURL' => $this->router->generate('amulen_nps_payment_receive', [], Router::ABSOLUTE_URL),
            'psp_FrmBackButtonURL' => $this->router->generate('order', [], Router::ABSOLUTE_URL),
            'psp_FrmLanguage' => $paymentInfo->getLanguage() ?? 'es_AR',
        ];
        try {

            $resp = $this->getNpsSdk()->payOnline3p($params);

            return $resp->psp_FrontPSP_URL;

        } catch (ApiException $e) {
            throw new GatewayException($e->getMessage());
        }
    }

    /**
     * @inheritdoc
     */
    public function validatePayment($paymentInfo)
    {
        $this->init();

        $response = new Response();
        $response->setMessage('Something goes wrong.');


        $resp = $this->getNpsSdk()->simpleQueryTx([
            'psp_Version' => '2.2',
            'psp_MerchantId' => $this->settings->get(Setting::KEY_MERCHANT_ID),
            'psp_QueryCriteria' => 'T',
            'psp_QueryCriteriaId' => $paymentInfo->getTransactionId(),
            'psp_PosDateTime' => date('Y-m-d H:i:s')
        ]);


        if ($resp->psp_ResponseCod == 2) {
            if ($resp->psp_Transaction->psp_ResponseCod == 0) {

                $response->setStatus(Status::APPROVED);
                $response->setOrderId($resp->psp_Transaction->psp_MerchOrderId);

            } else {
                $response->setStatus(Status::REJECTED);
                $response->setMessage($resp->psp_Transaction->psp_ResponseMsg);
            }
        }

        return $response;
    }

    /**
     * @return Sdk
     */
    public function getNpsSdk()
    {
        if (!$this->npsSdk) {
            $this->npsSdk = new Sdk();
        }
        return $this->npsSdk;
    }


}