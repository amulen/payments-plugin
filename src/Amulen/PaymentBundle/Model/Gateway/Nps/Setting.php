<?php

namespace Amulen\PaymentBundle\Model\Gateway\Nps;

/**
 * Nps settings.
 */
class Setting
{
    const KEY_SECRET_KEY = 'amulen_payment_nps_secret_key';
    const KEY_MERCHANT_ID = 'amulen_payment_nps_merchant_id';
    const KEY_ENVIRONMENT = 'amulen_payment_nps_environment';

    const ENVIRONMENT_DEV = 'development';
    const ENVIRONMENT_PROD = 'production';

}