<?php

namespace Amulen\PaymentBundle\Tests\Service\Paypal;

use Amulen\PaymentBundle\Tests\BaseTestCase;
use Amulen\PaymentBundle\Model\Payment\PaymentOrder;
use Flowcode\UserBundle\Entity\UserInterface;

class PaypalPaymentButtonGatewayTest extends BaseTestCase {

    protected $paypalPaymentButtonGateway;

    public function setUp() {
        parent::setUp();
        $this->paypalPaymentButtonGateway = $this->getContainer()->get('amulen_payment.gateway.button.paypal');
        $this->paypalPaymentInfoBuilder = $this->getContainer()->get('amulen_payment.builder.paypal');
    }

    public function testGetLinkUrl_withPaymentInfoOk_getLinkUrl() {
        $paymentOrder = new PaymentOrder();
        $orderId = 1;
        $total = 1;
        $userEmail = 'user@user.com';
        $paymentOrder->setId($orderId);
        $paymentOrder->setTotal($total);
        $paymentOrder->setBrandName('a name');
        $paymentOrder->setBrandLogo("https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcSlFCgVwIsaFHRByh2Pt83tVOFx-oDSts1iJOB6AUKEyhzn66X5F5zx1s8");
        $paymentOrder->setDescription('a description');
        $user = $this->getMockBuilder(UserInterface::class)
                ->getMock();
        $user
                ->method('getId')
                ->willReturn(1);
        $user
                ->method('getEmail')
                ->willReturn($userEmail);
        $paymentInfo = $this->paypalPaymentInfoBuilder->buildForButtonGateway($paymentOrder, $user);

        $this->assertNotNull($paymentInfo);
        $this->assertEquals($orderId, $paymentInfo->getOrderId());
        $this->assertEquals($total, $paymentInfo->getUnitPrice());
        $this->assertEquals($userEmail, $paymentInfo->getCustomerMail());
        $this->assertEquals(1, sizeof($paymentInfo->getPaymentInfoItems()));

        $url = $this->paypalPaymentButtonGateway->getLinkUrl($paymentInfo);
        var_dump($url);
        $this->assertNotNull($url);
    }

}
