<?php

namespace Amulen\PaymentBundle\Model\Gateway\Mp;

/**
 * MP settings.
 */
class Setting
{
    const KEY_SECRET_KEY = 'amulen_payment_mp_secret_key';
    const KEY_MERCHANT_ID = 'amulen_payment_mp_merchant_id';
    const KEY_ENVIRONMENT = 'amulen_payment_mp_environment';

    const ENVIRONMENT_DEV = 'development';
    const ENVIRONMENT_PROD = 'production';

    const GATEWAY_ID = 'mercadopago';

    const CURRENCY_PESO = 'ARS';
    const CURRENCY_DOLLAR = 'USD';

}