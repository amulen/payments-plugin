<?php


namespace Amulen\PaymentBundle\Model\Payment;

/**
 * Payment status.
 */
class Status
{
    const PENDING = 'pending';
    const APPROVED = 'approved';
    const REJECTED = 'rejected';
    const CANCELED = 'canceled';
}