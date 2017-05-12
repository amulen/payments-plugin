<?php

namespace Amulen\PaymentBundle\Service\Nps;


use Amulen\NpsBundle\Model\Client\SoapClient;
use Amulen\NpsBundle\Model\Client\SoapClientFactory;
use Amulen\NpsBundle\Model\Exception\ApiException;
use Amulen\NpsBundle\Model\Soap\Operation;
use Amulen\NpsBundle\Service\PaymentService;
use Amulen\PaymentBundle\Model\Exception\GatewayException;
use Amulen\PaymentBundle\Model\Gateway\Nps\Setting;
use Amulen\PaymentBundle\Model\Gateway\PaymentButtonGateway;
use Amulen\PaymentBundle\Model\Gateway\Response;
use Amulen\PaymentBundle\Model\Payment\Status;
use Amulen\SettingsBundle\Model\SettingRepository;
use Symfony\Component\Routing\Router;

/**
 * Nps buttons payments gateway.
 */
class NpsPaymentButtonGateway implements PaymentButtonGateway
{

    /**
     * @var PaymentService
     */
    private $npsSdk;

    /**
     * @var Router
     */
    private $router;

    /**
     * @var SettingRepository
     */
    private $settings;

    /**
     * @var SoapClientFactory
     */
    private $soapClientFactory;

    /**
     * PaymentService constructor.
     * @param Router $router
     */
    public function __construct(Router $router, SettingRepository $settingRepository, $soapClientFactory)
    {
        $this->router = $router;
        $this->settings = $settingRepository;
        $this->soapClientFactory = $soapClientFactory;
    }

    /**
     * @inheritdoc
     */
    public function getLinkUrl($paymentInfo)
    {

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
            'psp_ReturnURL' => $this->router->generate('amulen_payment_notification_nps', [], Router::ABSOLUTE_URL),
            'psp_FrmBackButtonURL' => $this->router->generate('order', [], Router::ABSOLUTE_URL),
            'psp_FrmLanguage' => $paymentInfo->getLanguage() ?? 'es_AR',
        ];
        try {
            $options = [];
            $wsdlRoute = $this->settings->get(Setting::KEY_WSDL_URL);
            if ($wsdlRoute) {
                $soapAction = str_replace("?wsdl", '/' . Operation::PAY_ONLINE_3P, $wsdlRoute);
                $options['soapaction'] = $soapAction;
            }

            $resp = $this->getNpsSdk()->payOnline3p($params, $options);

            if ($resp->psp_ResponseCod == "1") {

                return $resp->psp_FrontPSP_URL;

            } else {

                throw new GatewayException($resp->psp_ResponseExtended);
            }

        } catch (ApiException $e) {
            throw new GatewayException($e->getMessage());
        }
    }

    /**
     * @inheritdoc
     */
    public function validatePayment($paymentInfo)
    {

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
     * @return PaymentService
     */
    public function getNpsSdk()
    {
        if (!$this->npsSdk) {

            $this->npsSdk = new PaymentService();
            $client = $this->soapClientFactory->npsSoapClient();

            $this->npsSdk->setClient($client);
        }
        return $this->npsSdk;
    }


}