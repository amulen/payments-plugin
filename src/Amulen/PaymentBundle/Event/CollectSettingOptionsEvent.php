<?php
namespace Amulen\PaymentBundle\Event;

use Symfony\Component\EventDispatcher\Event;

class CollectSettingOptionsEvent extends Event
{
    const NAME = 'amulen.event.collect_setting_options';
    
    protected $settingOptions = [];
    
    /**
     * @param $settingOption
     */
    public function addSettingOption($settingOption)
    {
        array_push($this->settingOptions, $settingOption);
    }
    /**
     * @return mixed
     */
    public function getSettingOptions()
    {
        return $this->settingOptions;
    }
    /**
     * @param mixed $settingOptions
     */
    public function setSettingOptions($settingOptions)
    {
        $this->settingOptions = $settingOptions;
    }
}