<?php
namespace Amulen\PaymentBundle\Tests\Service\Paypal;

use Amulen\PaymentBundle\Tests\BaseTestCase;
use Amulen\PaymentBundle\Model\Payment\SubscriptionOrder;
use Flowcode\UserBundle\Entity\UserInterface;

class PaypalSubscriptionButtonGatewayTest extends BaseTestCase
{

    protected $paypalSubscriptionButtonGateway;

    public function setUp()
    {
        parent::setUp();
        $this->paypalSubscriptionButtonGateway = $this->getContainer()->get('amulen_payment.gateway.subscription.button.paypal');
        $this->paypalSubscriptionInfoBuilder = $this->getContainer()->get('amulen_payment.subscription.builder.paypal');
    }

    public function testGetLinkUrl_withSubscriptionInfoOk_getLinkUrl()
    {
        $subscriptionOrder = new SubscriptionOrder();
        $userEmail = 'user@user.com';
        $subscriptionOrder->setName('A name');
        $subscriptionOrder->setDescription('A description');
        $subscriptionOrder->setPlanId("P-33E819573F995234CLASII6A");
        $urlSarasa = 'https://devapi.cloudlance.co/api/amulen_payment/notification/paypalGato';
        $subscriptionOrder->setReturnUrl($urlSarasa);
        $subscriptionOrder->setCancelUrl($urlSarasa);
        $user = $this->getMockBuilder(UserInterface::class)
            ->getMock();
        $user
            ->method('getId')
            ->willReturn(1);
        $user
            ->method('getEmail')
            ->willReturn($userEmail);
        $subscriptionInfo = $this->paypalSubscriptionInfoBuilder->buildForButtonGateway($subscriptionOrder, $user);

        $url = $this->paypalSubscriptionButtonGateway->getLinkUrl($subscriptionInfo);
        $this->assertNotNull($url);
        var_dump($url);
    }

    public function testGetLinkUrl_withSubscriptionInfoWithInvalidPlan_throwException()
    {
        $subscriptionOrder = new SubscriptionOrder();
        $userEmail = 'user@user.com';
        $subscriptionOrder->setName('A name');
        $subscriptionOrder->setDescription('A description');
        $subscriptionOrder->setPlanId("idInvalid");
        $urlSarasa = 'https://devapi.cloudlance.co/api/amulen_payment/notification/paypalGato';
        $subscriptionOrder->setNotifyUrl($urlSarasa);
        $subscriptionOrder->setReturnUrl($urlSarasa);
        $subscriptionOrder->setCancelUrl($urlSarasa);
        $user = $this->getMockBuilder(UserInterface::class)
            ->getMock();
        $user
            ->method('getId')
            ->willReturn(1);
        $user
            ->method('getEmail')
            ->willReturn($userEmail);
        $subscriptionInfo = $this->paypalSubscriptionInfoBuilder->buildForButtonGateway($subscriptionOrder, $user);

        $this->setExpectedException(\InvalidArgumentException::class);
        $this->paypalSubscriptionButtonGateway->getLinkUrl($subscriptionInfo);
    }
    
    
    public function testConfirm_withTokenOk_confirmSubscription()
    {
        $subscriptionOrder = new SubscriptionOrder();
        $token = "EC-3KX921065E369583U";
        $this->paypalSubscriptionButtonGateway->confirmSubscription($token);
    }
}
