<?php

namespace Amulen\PaymentBundle\Event;

use Flowcode\DashboardBundle\Entity\Setting;
use Flowcode\DashboardBundle\Event\CollectSettingOptionsEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Translation\TranslatorInterface;


/**
 * CollectSettingOptionsSubscriber
 */
class CollectSettingOptionsSubscriber implements EventSubscriberInterface
{
    protected $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * Returns an array of event names this subscriber wants to listen to.
     *
     * @return array The event names to listen to
     */
    public static function getSubscribedEvents()
    {
        return array(
            CollectSettingOptionsEvent::NAME => array('handler', 1000),
        );
    }


    public function handler(CollectSettingOptionsEvent $event)
    {
        $event->addSettingOption([
            "key" => \Amulen\PaymentBundle\Model\Gateway\Nps\Setting::KEY_ENVIRONMENT,
            "label" => $this->translator->trans('Environment'),
        ]);

        $event->addSettingOption([
            "key" => \Amulen\PaymentBundle\Model\Gateway\Nps\Setting::KEY_WSDL_URL,
            "label" => $this->translator->trans('Wsdl Url'),
        ]);

        $event->addSettingOption([
            "key" => \Amulen\PaymentBundle\Model\Gateway\Nps\Setting::KEY_MERCHANT_ID,
            "label" => $this->translator->trans('Merchant ID'),
        ]);

        $event->addSettingOption([
            "key" => \Amulen\PaymentBundle\Model\Gateway\Nps\Setting::KEY_SECRET_KEY,
            "label" => $this->translator->trans('Secret Key'),
        ]);
    }
}