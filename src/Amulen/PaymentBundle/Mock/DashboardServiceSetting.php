<?php
namespace Amulen\PaymentBundle\Mock;

use Amulen\SettingsBundle\Model\SettingRepository;
use Symfony\Component\DependencyInjection\Container;

class DashboardServiceSetting implements SettingRepository
{

    protected $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }


    /**
     * Get a setting value.
     *
     * @param $key string
     * @return string|null
     */
    public function get($key)
    {
        return $this->container->hasParameter($key) ? $this->container->getParameter($key) : null;
    }

    /**
     * Get all settings.
     */
    public function getAll()
    {
        return $this->container->getParameterBag()->all();
    }
}