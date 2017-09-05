<?php
namespace Amulen\PaymentBundle\Tests\Service\Paypal;

use Amulen\PaymentBundle\Tests\BaseTestCase;
use Amulen\PaymentBundle\Model\Payment\PaymentOrder;
use Flowcode\UserBundle\Entity\UserInterface;
use Amulen\PaymentBundle\Model\Gateway\Paypal\PaypalPlan;
use Amulen\PaymentBundle\Model\Gateway\Paypal\PaypalPlanPayment;

class PaypalPaymentButtonGatewayTest extends BaseTestCase
{

    protected $paypalPaymentButtonGateway;

    public function setUp()
    {
        parent::setUp();
        $this->paypalService = $this->getContainer()->get('amulen_payment.paypal.service');
    }

    public function testCreatePlan_withPaypalPlanOk_createPlan()
    {
        $paypalPlan = new PaypalPlan();
        $paypalPlan->setName('Suscripcion mensual paypal');
        $paypalPlan->setDescription('Suscripcion mensual paypal descripcion');
        $payments = array();
        $paymentOffer = new PaypalPlanPayment();
        $paymentOffer->setName("Pago promocional primeros 3 meses");
        $paymentOffer->setFrequencyInterval(1);
        $paymentOffer->setCycles(3);
        $paymentOffer->setAmount(10);
        $paymentOffer->setType('TRIAL');
        array_push($payments, $paymentOffer);
        $paymentOrdinary = new PaypalPlanPayment();
        $paymentOrdinary->setName("Pago ordinario");
        $paymentOrdinary->setFrequencyInterval(1);
        $paymentOrdinary->setCycles(0);
        $paymentOrdinary->setAmount(15);
        $paymentOrdinary->setType('REGULAR');
        array_push($payments, $paymentOrdinary);
        $paypalPlan->setPayments($payments);
        $id = $this->paypalService->createPlan($paypalPlan);
        $this->assertNotNull($id);
    }
}
