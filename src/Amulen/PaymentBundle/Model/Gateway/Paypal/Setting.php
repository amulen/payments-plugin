<?php
namespace Amulen\PaymentBundle\Model\Gateway\Paypal;

/**
 * Paypal settings.
 */
class Setting
{

    const KEY_SECRET_KEY = 'amulen_payment_paypal_secret_key';
    const KEY_MERCHANT_ID = 'amulen_payment_paypal_merchant_id';
    const KEY_ENVIRONMENT = 'amulen_payment_paypal_sandbox_boolean';
    const ENVIRONMENT_DEV = 'development';
    const ENVIRONMENT_PROD = 'production';
    const GATEWAY_ID = 'paypal';
    const CURRENCY_PESO = 'ARS';
    const CURRENCY_DOLLAR = 'USD';

}
