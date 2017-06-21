<?php
namespace Amulen\PaymentBundle\Tests;

use Liip\FunctionalTestBundle\Test\WebTestCase;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Cloudlance\ApiBundle\Service\SecurityService;

class BaseTestCase extends WebTestCase
{

    protected $client;
    protected $em;
    protected $securityService;
    protected $userService;

    public function setUp()
    {
    }

    public function createAuthenticatedClient($username, $password)
    {

    }

    /**
     * El bojetivo es librerar memoria.
     * {@inheritdoc}
     */
    protected function tearDown()
    {

        $em = $this->getContainer()->get('doctrine')->getManager();
        $em->getConnection()->close();
        // do cleanup to release memory in test environment
        gc_disable();
        $container = $this->getContainer();
        $this->cleanupContainer($container);
        parent::tearDown();
        gc_collect_cycles();
        gc_enable();

        /* $publicDocumentsPath = $this->getContainer()->getParameter('public_documents_path');
          if (is_dir($publicDocumentsPath)) {
          (new Filesystem)->remove($publicDocumentsPath);
          } */
    }

    /**
     * Remove all container references from all loaded services
     */
    protected function cleanupContainer($container, $exclude = ['kernel'])
    {
        $object = new \ReflectionObject($container);
        $property = $object->getProperty('services');
        $property->setAccessible(true);

        $services = $property->getValue($container) ?: [];
        foreach ($services as $id => $service) {
            if (in_array($id, $exclude, true)) {
                continue;
            }

            $serviceObject = new \ReflectionObject($service);
            foreach ($serviceObject->getProperties() as $prop) {
                $prop->setAccessible(true);

                if ($prop->isStatic()) {
                    continue;
                }

                $prop->setValue($service, null);
            }
        }

        $property->setValue($container, null);
    }

    protected function mockSecurityService($user)
    {
        $tokenStorage = $this->getContainer()->get("security.token_storage");
        $this->securityService = $this->getMockBuilder(SecurityService::class)
            ->setMethods(['getUserLogedin'])
            ->setConstructorArgs(array($tokenStorage))
            ->getMock();
        $this->securityService
            ->method('getUserLogedin')
            ->willReturn($user);
        $this->getContainer()->set('cloudlance.security', $this->securityService);
    }
}
